<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\TestNiveau;
use App\Models\TestNiveauResultat;
use Illuminate\Http\Request;

class TestNiveauAdminController extends Controller
{
    public function index()
    {
        $tests = TestNiveau::withCount('questions')->get();

        $domaines = ['logistique', 'douane', 'commerce', 'transport', 'transport_logistique'];
        $niveaux = ['debutant', 'intermediaire', 'avance'];

        foreach ($domaines as $domaine) {
            foreach ($niveaux as $niveau) {
                $exists = $tests->where('domaine', $domaine)->where('niveau', $niveau)->first();
                if (!$exists) {
                    $test = TestNiveau::create([
                        'domaine' => $domaine,
                        'niveau' => $niveau,
                        'titre' => ucfirst($domaine) . ' - ' . ucfirst($niveau),
                        'nb_questions' => 10,
                        'is_active' => true,
                    ]);
                    $test->questions_count = 0;
                    $tests->push($test);
                }
            }
        }

        $tests = $tests->groupBy('domaine');

        return view('admin.test-niveau.index', compact('tests', 'domaines', 'niveaux'));
    }

    public function storeQuestion(Request $request)
    {
        $data = $request->validate([
            'test_niveau_id' => 'required|exists:tests_niveau,id',
            'texte' => 'required|string',
            'type' => 'required|in:qcm,vrai_faux',
            'reponses' => 'required|array|min:2',
            'reponses.*.texte' => 'required|string',
            'reponses.*.is_correct' => 'nullable',
        ]);
        \App\Support\Reponses::exigerBonneReponse($data['reponses']);

        $test = TestNiveau::findOrFail($data['test_niveau_id']);

        $question = $test->questions()->create([
            'texte' => $data['texte'],
            'type' => $data['type'],
            'points' => 1,
            'ordre' => $test->questions()->count() + 1,
        ]);

        foreach ($data['reponses'] as $rep) {
            $question->reponses()->create([
                'texte' => $rep['texte'],
                'is_correct' => isset($rep['is_correct']),
            ]);
        }

        return back()->with('success', 'Question ajoutee au test de niveau.');
    }

    public function destroyQuestion(Question $question)
    {
        $question->reponses()->delete();
        $question->delete();
        return back()->with('success', 'Question supprimee.');
    }

    public function resultats()
    {
        $resultats = TestNiveauResultat::with('user:id,name,prenom,email')
            ->latest('created_at')
            ->paginate(50);

        return view('admin.test-niveau.resultats', compact('resultats'));
    }
}
