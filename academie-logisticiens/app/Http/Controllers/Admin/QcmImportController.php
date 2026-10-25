<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Challenge;
use App\Models\Examen;
use App\Models\Formation;
use App\Models\Module;
use App\Models\TestNiveau;
use App\Services\GeminiContentParser;
use Illuminate\Http\Request;

class QcmImportController extends Controller
{
    public function index()
    {
        $groupes = [];

        $formations = Formation::with('modules.quiz')->orderBy('titre')->get();
        foreach ($formations as $f) {
            $groupes['Examens'][] = ['value' => "exam:{$f->id}:test", 'label' => "{$f->titre} - Examen Test"];
            $groupes['Examens'][] = ['value' => "exam:{$f->id}:final", 'label' => "{$f->titre} - Examen Final"];
        }
        foreach ($formations as $f) {
            foreach ($f->modules as $m) {
                $groupes['Quiz de module'][] = ['value' => "quiz:{$m->id}", 'label' => "{$f->titre} > {$m->titre}"];
            }
        }
        foreach (TestNiveau::orderBy('domaine')->orderBy('niveau')->get() as $t) {
            $groupes['Tests de niveau'][] = ['value' => "niveau:{$t->id}", 'label' => ucfirst($t->domaine) . ' - ' . ucfirst($t->niveau)];
        }
        foreach (Challenge::with('parcours')->withCount('questions')->orderBy('parcours_id')->orderBy('ordre')->get() as $c) {
            $groupes['Etapes CTF'][] = ['value' => "etape:{$c->id}", 'label' => "{$c->parcours->titre} > {$c->titre} ({$c->questions_count} q.)"];
        }

        return view('admin.qcm-import.index', [
            'groupes' => $groupes,
            'cible' => request('cible'),
            'aiDispo' => (bool) config('services.gemini.api_key'),
        ]);
    }

    public function analyze(Request $request)
    {
        $request->validate([
            'cible' => 'required|string',
            'mode' => 'required|in:ajouter,remplacer',
            'texte' => 'required|string',
        ], ['texte.required' => 'Collez vos questions dans la zone de texte.']);

        $this->resolveTarget($request->cible, false);
        $questions = $this->parseTexte($request->texte);

        if (empty($questions)) {
            return back()->with('error', 'Aucune question valide detectee dans le texte.')->withInput();
        }

        $request->session()->put('qcm_import', [
            'cible' => $request->cible,
            'mode' => $request->mode,
            'questions' => $questions,
        ]);

        return redirect()->route('admin.qcm-import.apercu');
    }

    public function apercu(Request $request)
    {
        $import = $request->session()->get('qcm_import');
        if (!$import) {
            return redirect()->route('admin.qcm-import.index');
        }

        [, $label] = $this->resolveTarget($import['cible'], false);

        return view('admin.qcm-import.preview', [
            'label' => $label,
            'mode' => $import['mode'],
            'questions' => $import['questions'],
            'minimum' => str_starts_with($import['cible'], 'etape:') ? Challenge::MIN_QUESTIONS : 0,
        ]);
    }

    /** Etape 1 (AJAX) : envoie les fichiers a Gemini. */
    public function lancer(Request $request)
    {
        $request->validate([
            'cible' => 'required|string',
            'mode' => 'required|in:ajouter,remplacer',
            'documents' => 'required|array|min:1|max:10',
            'documents.*' => 'file|mimes:pdf,docx,doc,pptx,ppt,txt|max:51200',
        ], ['documents.required' => 'Selectionnez au moins un fichier Word ou PDF.']);

        if (!config('services.gemini.api_key')) {
            return response()->json(['message' => 'Cle API Gemini non configuree (GEMINI_API_KEY dans .env).'], 422);
        }

        set_time_limit(300);
        [$target] = $this->resolveTarget($request->cible, false);

        try {
            $uris = (new GeminiContentParser())->uploadFiles($request->file('documents'));
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $request->session()->put('qcm_ai', [
            'uris' => $uris,
            'cible' => $request->cible,
            'mode' => $request->mode,
            'minimum' => str_starts_with($request->cible, 'etape:') ? Challenge::MIN_QUESTIONS : 0,
            'theme' => $target instanceof Challenge ? (string) ($target->theme ?: $target->titre) : '',
            'next' => 1,
            'lots' => 0,
            'questions' => [],
        ]);

        return response()->json(['ok' => true]);
    }

    /** Etape 2 (AJAX, repetee) : extrait un lot de 60 questions. */
    public function lot(Request $request)
    {
        $etat = $request->session()->get('qcm_ai');
        if (!$etat) {
            return response()->json(['message' => 'Session d\'analyse expiree. Recommencez.'], 422);
        }

        set_time_limit(300);
        $taille = 40;

        try {
            $res = (new GeminiContentParser())->extractBatch($etat['uris'], $etat['next'], $taille, $etat['minimum'], $etat['theme']);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage(), 'total' => count($etat['questions'])], 422);
        }

        $etat['questions'] = array_merge($etat['questions'], $res['questions']);
        $etat['next'] += $taille;
        $etat['lots']++;
        $request->session()->put('qcm_ai', $etat);

        $fini = $res['brut'] < 10 || $etat['lots'] >= 40;

        return response()->json(['total' => count($etat['questions']), 'lot' => count($res['questions']), 'fini' => $fini]);
    }

    /** Etape 3 (AJAX) : nettoie Gemini et prepare l'apercu. */
    public function terminer(Request $request)
    {
        $etat = $request->session()->pull('qcm_ai');
        if (!$etat) {
            return response()->json(['message' => 'Session d\'analyse expiree.'], 422);
        }

        (new GeminiContentParser())->deleteFiles($etat['uris']);

        $vues = [];
        $questions = collect($etat['questions'])->filter(function ($q) use (&$vues) {
            $cle = mb_strtolower(preg_replace('/\s+/', ' ', $q['texte']));
            if (isset($vues[$cle])) {
                return false;
            }
            return $vues[$cle] = true;
        })->values()->all();

        if (empty($questions)) {
            return response()->json(['message' => 'Aucune question valide detectee dans les fichiers.'], 422);
        }

        $request->session()->put('qcm_import', [
            'cible' => $etat['cible'],
            'mode' => $etat['mode'],
            'questions' => $questions,
        ]);

        return response()->json(['url' => route('admin.qcm-import.apercu')]);
    }

    public function save(Request $request)
    {
        $import = $request->session()->get('qcm_import');
        if (!$import) {
            return redirect()->route('admin.qcm-import.index')->with('error', 'Session d\'import expiree. Recommencez.');
        }

        $keep = collect($request->input('keep', []))->map(fn($i) => (int) $i)->all();
        $questions = collect($import['questions'])->filter(fn($q, $i) => in_array($i, $keep, true))->values();

        if ($questions->isEmpty()) {
            return back()->with('error', 'Selectionnez au moins une question.');
        }

        [$target] = $this->resolveTarget($import['cible'], true);

        if ($import['mode'] === 'remplacer') {
            $target->questions()->get()->each->delete();
        }

        $ordre = $target->questions()->count();
        foreach ($questions as $q) {
            $question = $target->questions()->create([
                'texte' => $q['texte'],
                'type' => $q['type'] ?? 'qcm',
                'points' => 1,
                'ordre' => ++$ordre,
            ]);
            foreach ($q['reponses'] as $r) {
                $question->reponses()->create(['texte' => $r['texte'], 'is_correct' => (bool) $r['is_correct']]);
            }
        }

        if ($target instanceof Examen) {
            $target->update(['nb_questions' => min($target->nb_questions, max(1, $target->questions()->count()))]);
        }

        $request->session()->forget('qcm_import');
        $total = $target->questions()->count();

        return redirect()->route('admin.qcm-import.index', ['cible' => $import['cible']])
            ->with('success', $questions->count() . ' question(s) importee(s). Total dans cette destination : ' . $total . '.');
    }

    private function resolveTarget(string $cible, bool $create): array
    {
        $parts = explode(':', $cible);

        switch ($parts[0]) {
            case 'exam':
                $formation = Formation::findOrFail($parts[1]);
                $type = in_array($parts[2] ?? '', ['test', 'final'], true) ? $parts[2] : abort(404);
                $label = $formation->titre . ' - Examen ' . ($type === 'test' ? 'Test' : 'Final');
                if (!$create) {
                    return [Examen::where('formation_id', $formation->id)->where('type', $type)->first() ?? new Examen, $label];
                }
                $examen = Examen::firstOrCreate(
                    ['formation_id' => $formation->id, 'type' => $type],
                    [
                        'titre' => $type === 'test' ? 'Examen Test' : 'Examen Final',
                        'duree_minutes' => $type === 'test' ? 60 : 80,
                        'score_minimum' => 70,
                        'nb_tentatives_max' => $type === 'test' ? 9999 : 1,
                        'nb_questions' => $type === 'test' ? 10 : 20,
                    ]
                );
                return [$examen, $label];

            case 'quiz':
                $module = Module::with('formation')->findOrFail($parts[1]);
                $label = $module->formation->titre . ' > ' . $module->titre . ' (quiz)';
                $quiz = $create ? ($module->quiz ?? $module->quiz()->create(['titre' => 'Quiz - ' . $module->titre])) : ($module->quiz ?? new \App\Models\Quiz);
                return [$quiz, $label];

            case 'niveau':
                $t = TestNiveau::findOrFail($parts[1]);
                return [$t, 'Test de niveau ' . ucfirst($t->domaine) . ' - ' . ucfirst($t->niveau)];

            case 'etape':
                $c = Challenge::with('parcours')->findOrFail($parts[1]);
                return [$c, $c->parcours->titre . ' > ' . $c->titre];
        }

        abort(404);
    }

    private function parseTexte(string $text): array
    {
        $questions = [];
        $blocks = preg_split('/\n\s*\n/', str_replace("\r\n", "\n", trim($text)));

        foreach ($blocks as $block) {
            $lines = array_values(array_filter(array_map('trim', explode("\n", trim($block)))));
            if (count($lines) < 3) {
                continue;
            }

            $texte = preg_replace('/^\d+[\.\)\-]\s*/', '', $lines[0]);
            $reponses = [];
            $hasCorrect = false;

            for ($i = 1; $i < count($lines); $i++) {
                $line = $lines[$i];
                $isCorrect = false;
                if (preg_match('/^\*\s*/', $line)) {
                    $isCorrect = true;
                    $line = preg_replace('/^\*\s*/', '', $line);
                }
                if (preg_match('/\s*\*\s*$/', $line)) {
                    $isCorrect = true;
                    $line = preg_replace('/\s*\*\s*$/', '', $line);
                }
                $line = trim(preg_replace('/^[A-Da-d][\.\)\-]\s*/', '', $line));
                if ($line === '') {
                    continue;
                }
                $hasCorrect = $hasCorrect || $isCorrect;
                $reponses[] = ['texte' => $line, 'is_correct' => $isCorrect];
            }

            if (count($reponses) < 2) {
                continue;
            }
            if (!$hasCorrect) {
                $reponses[0]['is_correct'] = true;
            }

            $questions[] = ['texte' => $texte, 'type' => 'qcm', 'reponses' => $reponses];
        }

        return $questions;
    }
}
