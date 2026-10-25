<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Formation;
use App\Models\Module;
use App\Models\Lecon;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\Reponse;
use Illuminate\Http\Request;

class ContenuController extends Controller
{
    public function show(Formation $formation)
    {
        $formation->load(['modules.lecons', 'modules.quiz.questions.reponses']);
        return view('admin.formations.contenu', compact('formation'));
    }

    public function storeModule(Request $request, Formation $formation)
    {
        $data = $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $data['ordre'] = $formation->modules()->count() + 1;
        $formation->modules()->create($data);

        return back()->with('success', 'Module ajouté.');
    }

    public function updateModule(Request $request, Module $module)
    {
        $module->update($request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]));

        return back()->with('success', 'Module mis à jour.');
    }

    public function destroyModule(Module $module)
    {
        $module->quiz?->delete();
        $module->delete();
        return back()->with('success', 'Module supprimé.');
    }

    public function storeLecon(Request $request, Module $module)
    {
        $data = $request->validate([
            'titre' => 'required|string|max:255',
            'contenu_texte' => 'nullable|string',
            'video_url' => 'nullable|url|max:500',
            'duree_minutes' => 'nullable|integer|min:1',
            'is_preview' => 'boolean',
        ]);

        $data['is_preview'] = $request->boolean('is_preview');
        $data['ordre'] = $module->lecons()->count() + 1;
        $module->lecons()->create($data);

        return back()->with('success', 'Leçon ajoutée.');
    }

    public function updateLecon(Request $request, Lecon $lecon)
    {
        $data = $request->validate([
            'titre' => 'required|string|max:255',
            'contenu_texte' => 'nullable|string',
            'video_url' => 'nullable|url|max:500',
            'duree_minutes' => 'nullable|integer|min:1',
            'is_preview' => 'boolean',
        ]);

        $data['is_preview'] = $request->boolean('is_preview');
        $lecon->update($data);

        return back()->with('success', 'Leçon mise à jour.');
    }

    public function destroyLecon(Lecon $lecon)
    {
        $lecon->delete();
        return back()->with('success', 'Leçon supprimée.');
    }

    public function storeQuiz(Request $request, Module $module)
    {
        if ($module->quiz) {
            return back()->with('error', 'Ce module a déjà un quiz.');
        }

        $quiz = $module->quiz()->create([
            'titre' => $request->input('titre', 'Quiz — ' . $module->titre),
        ]);

        return back()->with('success', 'Quiz créé.');
    }

    public function storeQuestion(Request $request, Quiz $quiz)
    {
        $data = $request->validate([
            'texte' => 'required|string',
            'type' => 'required|in:qcm,vrai_faux',
            'points' => 'integer|min:1',
            'reponses' => 'required|array|min:2',
            'reponses.*.texte' => 'required|string',
            'reponses.*.is_correct' => 'nullable',
        ]);
        \App\Support\Reponses::exigerBonneReponse($data['reponses']);

        $question = $quiz->questions()->create([
            'texte' => $data['texte'],
            'type' => $data['type'],
            'points' => $data['points'] ?? 1,
            'ordre' => $quiz->questions()->count() + 1,
        ]);

        foreach ($data['reponses'] as $rep) {
            $question->reponses()->create([
                'texte' => $rep['texte'],
                'is_correct' => isset($rep['is_correct']),
            ]);
        }

        return back()->with('success', 'Question ajoutée.');
    }

    public function destroyQuestion(Question $question)
    {
        $question->reponses()->delete();
        $question->delete();
        return back()->with('success', 'Question supprimée.');
    }
}
