<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Parametre;
use App\Models\TestNiveau;
use App\Services\GamificationService;
use App\Services\OrientationIAService;
use Illuminate\Http\Request;

class TestNiveauController extends Controller
{
    public function index()
    {
        return view('public.tests.index');
    }

    public function questions(Request $request)
    {
        $request->validate([
            'domaine' => 'required|in:logistique,commerce,douane,transport,transport_logistique',
            'niveau' => 'required|in:debutant,intermediaire,avance',
        ]);

        $test =TestNiveau::where('domaine', $request->domaine)
            ->where('niveau', $request->niveau)
            ->where('is_active', true)
            ->first();

        if (!$test || $test->questions()->count() === 0) {
            return response()->json(['questions' => [], 'message' => 'Aucune question disponible pour ce domaine/niveau.']);
        }

        $nbQuestions = min($test->nb_questions, $test->questions()->count());
        $tirage = $test->questions()
            ->with('reponses:id,question_id,texte')
            ->inRandomOrder()
            ->take($nbQuestions)
            ->get();

        $request->session()->put('test_niveau_questions', $tirage->pluck('id')->all());

        $questions = $tirage->map(fn($q) => [
                'id' => $q->id,
                'texte' => $q->texte,
                'reponses' => $q->reponses->shuffle()->map(fn($r) => [
                    'id' => $r->id,
                    'texte' => $r->texte,
                ]),
            ]);

        return response()->json(['questions' => $questions]);
    }

    public function soumettre(Request $request)
    {
        $request->validate([
            'domaine' => 'required|in:logistique,commerce,douane,transport,transport_logistique',
            'niveau' => 'required|in:debutant,intermediaire,avance',
            'reponses' => 'nullable|array',
        ]);

        $ids = $request->session()->pull('test_niveau_questions');
        if (!$ids) {
            return redirect()->route('tests.index');
        }

        $scoreMin = (int) Parametre::valeur('score_minimum_defaut', 70);
        $questions = \App\Models\Question::with('reponses')->whereIn('id', $ids)->get();
        $total = $questions->count();
        $correct = $questions->filter(function ($q) use ($request) {
            $rid = $request->reponses[$q->id] ?? null;
            return $rid && $q->reponses->where('id', (int) $rid)->where('is_correct', true)->isNotEmpty();
        })->count();

        $score = $total > 0 ? (int) round(($correct / $total) * 100) : 0;
        $passed = $score >= $scoreMin;

        $dejaGagneAujourdhui = auth()->check() && \App\Models\TestNiveauResultat::where('user_id', auth()->id())
            ->where('domaine', $request->domaine)->where('niveau', $request->niveau)
            ->where('passed', true)->where('created_at', '>=', now()->subDay())->exists();

        \App\Models\TestNiveauResultat::create([
            'user_id' => auth()->id(),
            'domaine' => $request->domaine,
            'niveau' => $request->niveau,
            'score' => $score,
            'reponses_json' => $request->reponses,
            'passed' => $passed,
            'created_at' => now(),
        ]);

        if ($passed && auth()->check() && !$dejaGagneAujourdhui) {
            app(GamificationService::class)->testReussi(auth()->user());
        }

        $orientation = app(OrientationIAService::class);
        $recommandation = $orientation->recommander($request->domaine, $request->niveau, $score);

        return view('public.tests.resultat', compact('score', 'passed', 'recommandation'));
    }
}
