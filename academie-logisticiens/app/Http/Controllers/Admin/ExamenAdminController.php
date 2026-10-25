<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Examen;
use App\Models\Formation;
use App\Models\Question;
use App\Models\TentativeExamen;
use Illuminate\Http\Request;

class ExamenAdminController extends Controller
{
    public function index(Formation $formation)
    {
        $formation->load([
            'examens.questions.reponses',
        ]);

        $examenTest = $formation->examens->where('type', 'test')->first();
        $examenFinal = $formation->examens->where('type', 'final')->first();

        $tentativesFinales = collect();
        if ($examenFinal) {
            $tentativesFinales = TentativeExamen::where('examen_id', $examenFinal->id)
                ->with('user:id,name,prenom,email')
                ->where('is_active', true)
                ->where('is_passed', false)
                ->orderByDesc('created_at')
                ->get()
                ->groupBy('user_id')
                ->map(fn($group) => $group->first());
        }

        return view('admin.formations.examens', compact('formation', 'examenTest', 'examenFinal', 'tentativesFinales'));
    }

    public function storeOrUpdate(Request $request, Formation $formation)
    {
        $data = $request->validate([
            'type' => 'required|in:test,final',
            'titre' => 'required|string|max:255',
            'duree_minutes' => 'required|integer|min:5',
            'score_minimum' => 'required|integer|min:1|max:100',
            'nb_questions' => 'required|integer|min:1',
            'nb_tentatives_max' => 'required|integer|min:1',
        ]);

        Examen::updateOrCreate(
            ['formation_id' => $formation->id, 'type' => $data['type']],
            $data
        );

        return back()->with('success', 'Examen ' . ($data['type'] === 'test' ? 'test' : 'final') . ' enregistre.');
    }

    public function storeQuestion(Request $request, Examen $examen)
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

        $question = $examen->questions()->create([
            'texte' => $data['texte'],
            'type' => $data['type'],
            'points' => $data['points'] ?? 1,
            'ordre' => $examen->questions()->count() + 1,
        ]);

        foreach ($data['reponses'] as $rep) {
            $question->reponses()->create([
                'texte' => $rep['texte'],
                'is_correct' => isset($rep['is_correct']),
            ]);
        }

        return back()->with('success', 'Question ajoutee au pool.');
    }

    public function destroyQuestion(Question $question)
    {
        $question->reponses()->delete();
        $question->delete();
        return back()->with('success', 'Question supprimee.');
    }

    public function reactiverTentative(TentativeExamen $tentativeExamen)
    {
        TentativeExamen::where('user_id', $tentativeExamen->user_id)
            ->where('examen_id', $tentativeExamen->examen_id)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        return back()->with('success', 'Tentative reactivee. L\'apprenant peut repasser l\'examen.');
    }
}
