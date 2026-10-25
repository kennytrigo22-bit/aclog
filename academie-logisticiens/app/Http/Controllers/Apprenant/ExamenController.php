<?php

namespace App\Http\Controllers\Apprenant;

use App\Http\Controllers\Controller;
use App\Models\Examen;
use App\Models\Parametre;
use App\Models\Progression;
use App\Models\Quiz;
use App\Models\TentativeExamen;
use App\Services\CertificatService;
use App\Services\FormationProgressService;
use Illuminate\Http\Request;

class ExamenController extends Controller
{
    private function exigerInscription($user, int $formationId): void
    {
        if ($user->role === 'admin') {
            return;
        }

        abort_unless(\App\Models\Inscription::where('user_id', $user->id)
            ->where('formation_id', $formationId)
            ->whereIn('statut', ['active', 'terminee'])
            ->exists(), 403);
    }

    public function quiz(Quiz $quiz)
    {
        $quiz->load('questions.reponses', 'module.formation');
        $this->exigerInscription(auth()->user(), $quiz->module->formation_id);
        return view('apprenant.examen.quiz', compact('quiz'));
    }

    public function soumettreQuiz(Request $request, Quiz $quiz)
    {
        $request->validate(['reponses' => 'required|array']);

        $quiz->load('questions.reponses', 'module');
        $this->exigerInscription(auth()->user(), $quiz->module->formation_id);
        $total = $quiz->questions->count();
        $correct = 0;

        foreach ($quiz->questions as $question) {
            $reponseId = $request->reponses[$question->id] ?? null;
            if ($reponseId && $question->reponses->where('id', $reponseId)->where('is_correct', true)->isNotEmpty()) {
                $correct++;
            }
        }

        $score = $total > 0 ? round(($correct / $total) * 100) : 0;
        $scoreMin = (int) Parametre::valeur('score_minimum_quiz', 70);
        $passed = $score >= $scoreMin;

        \App\Models\TentativeQuiz::create([
            'user_id' => auth()->id(),
            'quiz_id' => $quiz->id,
            'score' => $score,
            'reponses_donnees' => $request->reponses,
            'is_passed' => $passed,
            'created_at' => now(),
        ]);

        return view('apprenant.examen.quiz-resultat', compact('quiz', 'score', 'passed', 'correct', 'total'));
    }

    private function refuserSiVerrouille(Examen $examen, $user)
    {
        $examen->load('formation');
        $status = app(FormationProgressService::class)->status($user, $examen->formation);

        if (!$status['unlocked']) {
            return redirect()->route('apprenant.cours.index')
                ->with('error', 'Examen verrouille : ' . $status['lock_reason'] . '.');
        }

        return null;
    }

    public function examen(Examen $examen)
    {
        $user = auth()->user();

        if ($refus = $this->refuserSiVerrouille($examen, $user)) {
            return $refus;
        }

        $tentativesCount = TentativeExamen::where('user_id', $user->id)
            ->where('examen_id', $examen->id)
            ->where('is_active', true)
            ->count();

        if ($examen->isFinal() && $tentativesCount >= $examen->nb_tentatives_max) {
            return redirect()->route('apprenant.cours.index')
                ->with('error', 'Vous avez atteint le nombre maximum de tentatives pour cet examen final.');
        }

        $historique = TentativeExamen::where('user_id', $user->id)
            ->where('examen_id', $examen->id)
            ->where('is_active', true)
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        $nbQuestionsPool = $examen->questions()->count();

        return view('apprenant.examen.consignes', compact('examen', 'tentativesCount', 'historique', 'nbQuestionsPool'));
    }

    public function demarrer(Request $request, Examen $examen)
    {
        $user = auth()->user();

        if ($refus = $this->refuserSiVerrouille($examen, $user)) {
            return $refus;
        }

        $tentativesCount = TentativeExamen::where('user_id', $user->id)
            ->where('examen_id', $examen->id)
            ->where('is_active', true)
            ->count();

        if ($examen->isFinal() && $tentativesCount >= $examen->nb_tentatives_max) {
            return redirect()->route('apprenant.cours.index')
                ->with('error', 'Nombre maximum de tentatives atteint.');
        }

        $cleStart = "examen_start_{$examen->id}_{$user->id}";
        $enCours = $request->session()->get($cleStart);
        $reprise = $enCours && (now()->timestamp - $enCours) < $examen->duree_minutes * 60;

        if (!$reprise) {
            $nbQuestions = min($examen->nb_questions, $examen->questions()->count());
            $ids = $examen->questions()->inRandomOrder()->take($nbQuestions)->pluck('id')->all();

            if (empty($ids)) {
                return redirect()->route('apprenant.cours.index')->with('error', 'Cet examen ne contient pas encore de questions.');
            }

            $request->session()->put($cleStart, now()->timestamp);
            $request->session()->put("examen_questions_{$examen->id}_{$user->id}", $ids);
        }

        return redirect()->route('apprenant.examen.passer', $examen);
    }

    public function passer(Request $request, Examen $examen)
    {
        $user = auth()->user();

        if ($refus = $this->refuserSiVerrouille($examen, $user)) {
            return $refus;
        }

        $start = $request->session()->get("examen_start_{$examen->id}_{$user->id}");
        $ids = $request->session()->get("examen_questions_{$examen->id}_{$user->id}");

        if (!$start || !$ids) {
            return redirect()->route('apprenant.examen', $examen);
        }

        $secondesRestantes = max(0, $examen->duree_minutes * 60 - (now()->timestamp - $start));
        $questions = \App\Models\Question::with('reponses')->whereIn('id', $ids)->get()
            ->sortBy(fn($q) => array_search($q->id, $ids))->values();

        $tentativesCount = TentativeExamen::where('user_id', $user->id)
            ->where('examen_id', $examen->id)->where('is_active', true)->count();

        $examen->load('formation');

        return view('apprenant.examen.examen', compact('examen', 'questions', 'tentativesCount', 'secondesRestantes'));
    }

    public function soumettreExamen(Request $request, Examen $examen, CertificatService $certificatService)
    {
        $request->validate(['reponses' => 'nullable|array']);

        $user = auth()->user();
        $reponses = $request->reponses ?? [];

        if ($refus = $this->refuserSiVerrouille($examen, $user)) {
            return $refus;
        }

        $questionIds = $request->session()->pull("examen_questions_{$examen->id}_{$user->id}");
        $startTime = $request->session()->pull("examen_start_{$examen->id}_{$user->id}");
        if (!$startTime) {
            return redirect()->route('apprenant.examen', $examen)
                ->with('error', 'Session d\'examen expiree. Veuillez recommencer.');
        }
        $ecoule = now()->timestamp - $startTime;
        $tempsEcoule = $ecoule >= ($examen->duree_minutes * 60);
        // Tolerance de 2 minutes pour la soumission automatique ; au-dela l'examen est invalide.
        $enRetard = $ecoule > ($examen->duree_minutes * 60) + 120;

        $tentativesCount = TentativeExamen::where('user_id', $user->id)
            ->where('examen_id', $examen->id)
            ->where('is_active', true)
            ->count();

        if ($examen->isFinal() && $tentativesCount >= $examen->nb_tentatives_max) {
            return redirect()->route('apprenant.cours.index')
                ->with('error', 'Nombre maximum de tentatives atteint.');
        }

        $questions = \App\Models\Question::with('reponses')
            ->whereIn('id', $questionIds ?: collect($reponses)->keys())
            ->get();
        $total = $questions->count();
        $correct = 0;

        foreach ($questions as $question) {
            $reponseId = $reponses[$question->id] ?? null;
            if ($reponseId && $question->reponses->where('id', $reponseId)->where('is_correct', true)->isNotEmpty()) {
                $correct++;
            }
        }

        $score = $total > 0 ? round(($correct / $total) * 100) : 0;
        $passed = !$enRetard && $score >= $examen->score_minimum;

        TentativeExamen::create([
            'user_id' => $user->id,
            'examen_id' => $examen->id,
            'score' => $score,
            'reponses_donnees' => $reponses,
            'is_passed' => $passed,
            'numero_tentative' => $tentativesCount + 1,
            'is_active' => true,
            'created_at' => now(),
        ]);

        $examen->load('formation');
        $certificat = null;

        if ($passed && $examen->isFinal()) {
            $certificat = $certificatService->generer($user, $examen->formation, $score);
            $inscription = \App\Models\Inscription::where('user_id', $user->id)
                ->where('formation_id', $examen->formation_id)
                ->first();
            $inscription?->update(['statut' => 'terminee']);
        }

        return view('apprenant.examen.examen-resultat', compact('examen', 'score', 'passed', 'correct', 'total', 'certificat', 'tempsEcoule', 'enRetard'));
    }
}
