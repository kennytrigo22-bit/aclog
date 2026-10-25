<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiContentParser
{
    private string $apiKey;
    private string $model;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
        $this->model = config('services.gemini.model');
    }

    /**
     * @param UploadedFile|UploadedFile[] $files
     */
    public function parse(UploadedFile|array $files): array
    {
        $files = is_array($files) ? $files : [$files];
        $fileNames = implode(', ', array_map(fn($f) => $f->getClientOriginalName(), $files));

        return $this->run($files, $this->buildPrompt($fileNames));
    }

    /** Envoie les fichiers a Gemini (une seule fois) et retourne leurs references. */
    public function uploadFiles(array $files): array
    {
        $uris = [];
        try {
            foreach ($files as $file) {
                $uris[] = $this->uploadFileToGemini($file);
            }
        } catch (\Throwable $e) {
            $this->deleteFiles($uris);
            throw $e;
        }

        return $uris;
    }

    public function deleteFiles(array $uris): void
    {
        foreach ($uris as $uri) {
            $this->deleteGeminiFile($uri['name']);
        }
    }

    /**
     * Extrait un lot de questions QCM (la question numero $start a $start+$size-1 du document).
     *
     * @return array{questions: array, brut: int}
     */
    public function extractBatch(array $uris, int $start, int $size, int $minimum = 0, string $theme = ''): array
    {
        $data = $this->generate($uris, $this->buildQuestionsPrompt($minimum, $theme, $start, $size), true);

        $questions = [];
        foreach (($data['questions'] ?? []) as $q) {
            $texte = trim((string) ($q['texte'] ?? ''));
            $reponses = collect($q['reponses'] ?? [])
                ->map(fn($r) => ['texte' => trim((string) ($r['texte'] ?? '')), 'is_correct' => !empty($r['is_correct'])])
                ->filter(fn($r) => $r['texte'] !== '')
                ->values()
                ->all();

            if ($texte === '' || count($reponses) < 2) {
                continue;
            }
            if (!collect($reponses)->contains('is_correct', true)) {
                continue;
            }

            $questions[] = [
                'texte' => $texte,
                'type' => ($q['type'] ?? 'qcm') === 'vrai_faux' ? 'vrai_faux' : 'qcm',
                'reponses' => $reponses,
            ];
        }

        return ['questions' => $questions, 'brut' => count($data['questions'] ?? [])];
    }

    private function run(array $files, string $prompt): array
    {
        $uris = $this->uploadFiles($files);

        try {
            return $this->generate($uris, $prompt, false);
        } finally {
            $this->deleteFiles($uris);
        }
    }

    private function generate(array $uris, string $prompt, bool $tolerateTruncation): array
    {
        $parts = [];
        foreach ($uris as $uri) {
            $parts[] = ['file_data' => ['file_uri' => $uri['uri'], 'mime_type' => $uri['mime']]];
        }
        $parts[] = ['text' => $prompt];

        $response = null;
        foreach (array_unique([$this->model, 'gemini-3.7-flash', 'gemini-3.6-flash']) as $modele) {
            $config = [
                'temperature' => 0.2,
                'maxOutputTokens' => 65536,
                'responseMimeType' => 'application/json',
            ];
            // Extraction de questions : pas de "reflexion" du modele, beaucoup plus rapide.
            if ($tolerateTruncation) {
                $config['thinkingConfig'] = ['thinkingBudget' => 0];
            }

            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$modele}:generateContent";
            $envoyer = fn (array $cfg) => $this->http()->connectTimeout(30)->timeout(500)
                ->post($url, ['contents' => [['parts' => $parts]], 'generationConfig' => $cfg]);

            $response = $envoyer($config);
            if ($response->status() === 400 && isset($config['thinkingConfig'])) {
                unset($config['thinkingConfig']);
                $response = $envoyer($config);
            }

            if (!in_array($response->status(), [429, 500, 503], true)) {
                break;
            }
            Log::warning("Gemini {$modele} indisponible ({$response->status()}), modele suivant.");
        }

        if ($response->failed()) {
            Log::error('Gemini API error', ['status' => $response->status(), 'body' => $response->body()]);
            $msg = $response->json('error.message', 'Erreur inconnue');
            throw new \RuntimeException("Erreur API Gemini ({$response->status()}) : {$msg}");
        }

        $text = $response->json('candidates.0.content.parts.0.text', '');
        $text = preg_replace('/^```json\s*/i', '', trim($text));
        $text = preg_replace('/```\s*$/', '', $text);

        $data = json_decode(trim($text), true);

        if (json_last_error() !== JSON_ERROR_NONE && $tolerateTruncation) {
            $data = $this->repairTruncatedQuestions(trim($text));
        }

        if (!is_array($data)) {
            Log::error('Gemini JSON parse error', ['raw' => substr($text, 0, 2000)]);
            throw new \RuntimeException("L'IA a retourne un format invalide. Reessayez.");
        }

        return $data;
    }

    /** Client HTTP : verification TLS active, cle API en en-tete (jamais dans l'URL). */
    private function http(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::withHeaders(['x-goog-api-key' => $this->apiKey]);
    }

    private function repairTruncatedQuestions(string $text): ?array
    {
        $pos = strlen($text);
        for ($i = 0; $i < 400; $i++) {
            $pos = strrpos(substr($text, 0, $pos), '}');
            if ($pos === false) {
                return null;
            }
            $decoded = json_decode(substr($text, 0, $pos + 1) . ']}', true);
            if (is_array($decoded) && isset($decoded['questions'])) {
                return $decoded;
            }
        }

        return null;
    }

    private function buildQuestionsPrompt(int $minimum, string $theme, int $start, int $size): string
    {
        $themeLine = $theme !== '' ? "Theme de reference : {$theme}.\n" : '';
        $fin = $start + $size - 1;
        $lot = "LOT : extrais UNIQUEMENT les questions numerotees (ou, a defaut, classees dans l'ordre du document) de {$start} a {$fin} incluses. "
            . "Si le document contient moins de {$start} questions, retourne {\"questions\": []}.\n";
        $completer = ($minimum > 0 && $start === 1)
            ? "- Si le document contient MOINS de {$minimum} questions exploitables, genere des questions supplementaires pertinentes basees sur son contenu pour atteindre au moins {$minimum} questions.\n"
            : "- N'invente PAS de questions : extrais uniquement celles presentes dans le document (QCM, exercices, evaluations, vrai/faux). Les exercices ouverts doivent etre transformes en QCM.\n";

        return <<<PROMPT
Analyse le(s) document(s) joint(s) et extrais les questions d'evaluation (QCM, exercices, vrai/faux, cas pratiques) sous forme de JSON strict.
{$themeLine}{$lot}
Retourne UNIQUEMENT ce JSON :
{
  "questions": [
    {
      "texte": "Enonce de la question ?",
      "type": "qcm",
      "reponses": [
        {"texte": "Reponse A", "is_correct": false},
        {"texte": "Reponse B", "is_correct": true},
        {"texte": "Reponse C", "is_correct": false},
        {"texte": "Reponse D", "is_correct": false}
      ]
    }
  ]
}

Regles STRICTES :
- Extrais TOUTES les questions du lot demande, sans en omettre, dans l'ordre. Ne resume pas.
- Si le document contient un corrige / une grille de reponses, utilise-le pour marquer la bonne reponse. Sinon, determine la bonne reponse toi-meme.
- Chaque question a exactement UNE bonne reponse (is_correct = true).
- type "qcm" : 4 reponses (garde celles du document si elles sont presentes). type "vrai_faux" : 2 reponses "Vrai" et "Faux".
- Retire les lettres/numeros devant les reponses (A., B), 1-...) du champ texte.
{$completer}- Ecris en francais, sans markdown.
PROMPT;
    }

    private function uploadFileToGemini(UploadedFile $file): array
    {
        $mimeType = $file->getMimeType();
        $fileSize = $file->getSize();
        $displayName = $file->getClientOriginalName();

        $startResponse = $this->http()
            ->withHeaders([
                'X-Goog-Upload-Protocol' => 'resumable',
                'X-Goog-Upload-Command' => 'start',
                'X-Goog-Upload-Header-Content-Length' => $fileSize,
                'X-Goog-Upload-Header-Content-Type' => $mimeType,
            ])
            ->timeout(60)
            ->post("https://generativelanguage.googleapis.com/upload/v1beta/files", [
                'file' => ['display_name' => $displayName],
            ]);

        if ($startResponse->failed()) {
            throw new \RuntimeException("Echec de l'initialisation de l'upload pour {$displayName}");
        }

        $uploadUrl = $startResponse->header('X-Goog-Upload-URL');
        if (!$uploadUrl) {
            throw new \RuntimeException("URL d'upload non recue pour {$displayName}");
        }

        $fileContent = file_get_contents($file->getRealPath());

        $uploadResponse = $this->http()
            ->withHeaders([
                'Content-Length' => $fileSize,
                'X-Goog-Upload-Offset' => '0',
                'X-Goog-Upload-Command' => 'upload, finalize',
            ])
            ->timeout(120)
            ->withBody($fileContent, $mimeType)
            ->post($uploadUrl);

        if ($uploadResponse->failed()) {
            throw new \RuntimeException("Echec de l'upload du fichier {$displayName}");
        }

        $fileUri = $uploadResponse->json('file.uri');
        $fileName = $uploadResponse->json('file.name');
        $state = $uploadResponse->json('file.state');

        if (!$fileUri) {
            throw new \RuntimeException("URI du fichier non recu pour {$displayName}");
        }

        if ($state === 'PROCESSING') {
            $maxWait = 60;
            $waited = 0;
            while ($waited < $maxWait) {
                sleep(3);
                $waited += 3;
                $check = $this->http()
                    ->timeout(30)
                    ->get("https://generativelanguage.googleapis.com/v1beta/{$fileName}");
                if ($check->json('state') === 'ACTIVE') {
                    break;
                }
            }
        }

        return ['uri' => $fileUri, 'mime' => $mimeType, 'name' => $fileName];
    }

    private function deleteGeminiFile(string $fileName): void
    {
        try {
            $this->http()
                ->timeout(10)
                ->delete("https://generativelanguage.googleapis.com/v1beta/{$fileName}");
        } catch (\Throwable $e) {
            // Ignore
        }
    }

    private function buildPrompt(string $fileNames): string
    {
        return <<<PROMPT
Analyse ces documents de formation ({$fileNames}) et extrais TOUT le contenu structure en JSON strict.

Retourne UNIQUEMENT un JSON valide avec cette structure exacte :

{
  "titre_formation": "Titre de la formation",
  "description": "Description generale",
  "modules": [
    {
      "titre": "Titre du module",
      "description": "Description du module",
      "lecons": [
        {
          "titre": "Titre de la lecon",
          "contenu_texte": "Contenu detaille (garde le formatage, listes, etc.)",
          "video_url": null,
          "duree_minutes": 15
        }
      ],
      "quiz": {
        "questions": [
          {
            "texte": "Question ?",
            "type": "qcm",
            "reponses": [
              {"texte": "Reponse A", "is_correct": false},
              {"texte": "Reponse B", "is_correct": true},
              {"texte": "Reponse C", "is_correct": false},
              {"texte": "Reponse D", "is_correct": false}
            ]
          }
        ]
      }
    }
  ],
  "examen_test": {
    "questions": [
      {
        "texte": "Question d'examen test ?",
        "type": "qcm",
        "reponses": [
          {"texte": "Reponse A", "is_correct": false},
          {"texte": "Reponse B", "is_correct": true},
          {"texte": "Reponse C", "is_correct": false},
          {"texte": "Reponse D", "is_correct": false}
        ]
      }
    ]
  },
  "examen_final": {
    "questions": [
      {
        "texte": "Question d'examen final ?",
        "type": "qcm",
        "reponses": [
          {"texte": "Reponse A", "is_correct": false},
          {"texte": "Reponse B", "is_correct": true},
          {"texte": "Reponse C", "is_correct": false},
          {"texte": "Reponse D", "is_correct": false}
        ]
      }
    ]
  }
}

Regles STRICTES :
- Decoupe le contenu en modules logiques (chapitres, sections principales)
- Chaque module contient des lecons (sous-sections, slides, pages)
- Genere des questions quiz pertinentes pour chaque module (3-5 questions QCM par module)
- Si des questions d'examen, exercices, QCM, ou evaluations sont presentes dans les documents, place-les dans examen_test ET examen_final
- Pour examen_test : extrais TOUTES les questions trouvees dans les documents (exercices, QCM, evaluations). Si pas assez, genere des questions supplementaires basees sur le contenu. Objectif : au moins 30 questions
- Pour examen_final : meme chose, mais avec des questions plus difficiles et approfondies. Objectif : au moins 30 questions
- Si tu trouves des URLs YouTube dans le document, mets-les dans video_url
- Estime la duree en minutes pour chaque lecon
- Le contenu texte doit etre detaille et preserver le formatage original
- type de question : "qcm" ou "vrai_faux"
- Pour vrai_faux, 2 reponses : "Vrai" et "Faux"
- Chaque question QCM doit avoir exactement 4 reponses avec une seule bonne reponse
- Si les documents contiennent des exercices ou problemes, transforme-les en questions QCM
PROMPT;
    }
}
