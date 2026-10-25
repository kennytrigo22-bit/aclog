<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Examen;
use App\Models\Formation;
use App\Services\GeminiContentParser;
use Illuminate\Http\Request;

class GeminiImportController extends Controller
{
    public function showUpload(Formation $formation)
    {
        return view('admin.formations.gemini-upload', compact('formation'));
    }

    public function analyze(Request $request, Formation $formation)
    {
        $request->validate([
            'mode_contenu' => 'required|in:ajouter,remplacer',
            'mode_examens' => 'required|in:ajouter,remplacer',
            'documents' => 'required|array|min:1|max:10',
            'documents.*' => 'required|file|mimes:pdf,pptx,ppt,docx,doc|max:51200',
        ], [
            'documents.required' => 'Veuillez selectionner au moins un fichier.',
            'documents.*.mimes' => 'Formats acceptes : PDF, PowerPoint, Word.',
            'documents.*.max' => 'Chaque fichier ne doit pas depasser 50 Mo.',
            'documents.*.uploaded' => 'Le fichier n\'a pas pu etre uploade. Verifiez la taille du fichier.',
        ]);

        set_time_limit(600);

        if (!config('services.gemini.api_key')) {
            return back()->with('error', 'Cle API Gemini non configuree. Ajoutez GEMINI_API_KEY dans le fichier .env');
        }

        try {
            $parser = new GeminiContentParser();
            $data = $parser->parse($request->file('documents'));
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        $request->session()->put("gemini_import_{$formation->id}", $data);
        $request->session()->put("gemini_modes_{$formation->id}", [
            'contenu' => $request->mode_contenu,
            'examens' => $request->mode_examens,
        ]);
        $modes = $request->session()->get("gemini_modes_{$formation->id}");

        return view('admin.formations.gemini-preview', compact('formation', 'data', 'modes'));
    }

    public function save(Request $request, Formation $formation)
    {
        $data = $request->validate([
            'modules' => 'required|array|min:1',
            'modules.*.titre' => 'required|string|max:255',
            'modules.*.description' => 'nullable|string',
            'modules.*.lecons' => 'nullable|array',
            'modules.*.lecons.*.titre' => 'required|string|max:255',
            'modules.*.lecons.*.contenu_texte' => 'nullable|string',
            'modules.*.lecons.*.video_url' => 'nullable|string|max:500',
            'modules.*.lecons.*.duree_minutes' => 'nullable|integer|min:1',
            'modules.*.quiz' => 'nullable|array',
            'modules.*.quiz.questions' => 'nullable|array',
            'modules.*.quiz.questions.*.texte' => 'required|string',
            'modules.*.quiz.questions.*.type' => 'required|in:qcm,vrai_faux',
            'modules.*.quiz.questions.*.reponses' => 'required|array|min:2',
            'modules.*.quiz.questions.*.reponses.*.texte' => 'required|string',
            'modules.*.quiz.questions.*.reponses.*.is_correct' => 'nullable',
            'examen_test' => 'nullable|array',
            'examen_test.questions' => 'nullable|array',
            'examen_test.questions.*.texte' => 'required|string',
            'examen_test.questions.*.type' => 'required|in:qcm,vrai_faux',
            'examen_test.questions.*.reponses' => 'required|array|min:2',
            'examen_test.questions.*.reponses.*.texte' => 'required|string',
            'examen_test.questions.*.reponses.*.is_correct' => 'nullable',
            'examen_final' => 'nullable|array',
            'examen_final.questions' => 'nullable|array',
            'examen_final.questions.*.texte' => 'required|string',
            'examen_final.questions.*.type' => 'required|in:qcm,vrai_faux',
            'examen_final.questions.*.reponses' => 'required|array|min:2',
            'examen_final.questions.*.reponses.*.texte' => 'required|string',
            'examen_final.questions.*.reponses.*.is_correct' => 'nullable',
        ]);

        $modes = $request->session()->get("gemini_modes_{$formation->id}", ['contenu' => 'ajouter', 'examens' => 'ajouter']);

        if ($modes['contenu'] === 'remplacer') {
            foreach ($formation->modules()->with('quiz')->get() as $ancien) {
                $ancien->quiz?->delete();
                $ancien->delete();
            }
        }

        if ($modes['examens'] === 'remplacer') {
            foreach ($formation->examens()->get() as $examen) {
                if (!empty($data['examen_' . $examen->type]['questions'])) {
                    $examen->questions()->get()->each->delete();
                }
            }
        }

        $startOrdre = $formation->modules()->count();

        foreach ($data['modules'] as $mi => $moduleData) {
            $module = $formation->modules()->create([
                'titre' => $moduleData['titre'],
                'description' => $moduleData['description'] ?? null,
                'ordre' => $startOrdre + $mi + 1,
            ]);

            foreach (($moduleData['lecons'] ?? []) as $li => $leconData) {
                $module->lecons()->create([
                    'titre' => $leconData['titre'],
                    'contenu_texte' => $leconData['contenu_texte'] ?? null,
                    'video_url' => $leconData['video_url'] ?? null,
                    'duree_minutes' => $leconData['duree_minutes'] ?? null,
                    'ordre' => $li + 1,
                ]);
            }

            if (!empty($moduleData['quiz']['questions'])) {
                $quiz = $module->quiz()->create([
                    'titre' => 'Quiz - ' . $moduleData['titre'],
                ]);

                foreach ($moduleData['quiz']['questions'] as $qi => $qData) {
                    $question = $quiz->questions()->create([
                        'texte' => $qData['texte'],
                        'type' => $qData['type'],
                        'points' => 1,
                        'ordre' => $qi + 1,
                    ]);

                    foreach ($qData['reponses'] as $rData) {
                        $question->reponses()->create([
                            'texte' => $rData['texte'],
                            'is_correct' => isset($rData['is_correct']) && $rData['is_correct'],
                        ]);
                    }
                }
            }
        }

        $this->saveExamQuestions($formation, 'test', $data['examen_test'] ?? null);
        $this->saveExamQuestions($formation, 'final', $data['examen_final'] ?? null);

        $request->session()->forget(["gemini_import_{$formation->id}", "gemini_modes_{$formation->id}"]);

        $moduleCount = count($data['modules']);
        $testCount = count($data['examen_test']['questions'] ?? []);
        $finalCount = count($data['examen_final']['questions'] ?? []);

        return redirect()->route('admin.formations.contenu', $formation)
            ->with('success', "{$moduleCount} module(s), {$testCount} question(s) examen test, {$finalCount} question(s) examen final importes via l'IA.");
    }

    private function saveExamQuestions(Formation $formation, string $type, ?array $examData): void
    {
        if (empty($examData['questions'])) {
            return;
        }

        $examen = Examen::firstOrCreate(
            ['formation_id' => $formation->id, 'type' => $type],
            [
                'titre' => $type === 'test' ? 'Examen Test' : 'Examen Final',
                'duree_minutes' => $type === 'test' ? 60 : 80,
                'score_minimum' => 70,
                'nb_tentatives_max' => $type === 'test' ? 9999 : 1,
                'nb_questions' => min(count($examData['questions']), $type === 'test' ? 10 : 20),
            ]
        );

        $startOrdre = $examen->questions()->count();

        foreach ($examData['questions'] as $qi => $qData) {
            $question = $examen->questions()->create([
                'texte' => $qData['texte'],
                'type' => $qData['type'] ?? 'qcm',
                'points' => 1,
                'ordre' => $startOrdre + $qi + 1,
            ]);

            foreach ($qData['reponses'] as $rData) {
                $question->reponses()->create([
                    'texte' => $rData['texte'],
                    'is_correct' => isset($rData['is_correct']) && $rData['is_correct'],
                ]);
            }
        }
    }
}
