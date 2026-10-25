<?php

namespace App\Http\Controllers\Apprenant;

use App\Http\Controllers\Controller;
use App\Models\Challenge;
use App\Models\Parametre;
use App\Models\Parcours;
use App\Models\Question;
use App\Services\GamificationService;
use Illuminate\Http\Request;

class ChallengeController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $captures = $user->flagsCaptures()->pluck('challenge_id');

        $parcours = Parcours::where('is_active', true)
            ->with(['challenges' => fn($q) => $q->where('is_active', true)])
            ->orderBy('ordre')
            ->get()
            ->each(function ($p) use ($captures) {
                $p->total_etapes = $p->challenges->count();
                $p->etapes_capturees = $p->challenges->whereIn('id', $captures)->count();
            })
            ->groupBy('domaine');

        return view('apprenant.challenges.index', compact('parcours'));
    }

    public function parcours(Parcours $parcours)
    {
        abort_unless($parcours->is_active || auth()->user()->role === 'admin', 404);

        $etapes = $parcours->challenges()->where('is_active', true)->withCount('questions')->get();
        $captures = auth()->user()->flagsCaptures()->pluck('challenge_id');
        $admin = auth()->user()->role === 'admin';

        $precedentOk = true;
        foreach ($etapes as $etape) {
            $etape->capturee = $captures->contains($etape->id);
            $etape->jouable = $etape->questions_count >= Challenge::MIN_QUESTIONS;
            $etape->debloquee = $admin || $precedentOk;
            $precedentOk = $precedentOk && $etape->capturee;
        }

        $termine = $etapes->isNotEmpty() && $etapes->every->capturee;

        return view('apprenant.challenges.parcours', compact('parcours', 'etapes', 'termine'));
    }

    public function etape(Request $request, Challenge $challenge)
    {
        if ($refus = $this->verifierAcces($challenge)) {
            return $refus;
        }

        $challenge->load('parcours');
        $deja = auth()->user()->flagsCaptures()->where('challenge_id', $challenge->id)->exists();

        $nb = max(Challenge::MIN_QUESTIONS, (int) $challenge->nb_questions);
        $questions = $challenge->questions()->with('reponses:id,question_id,texte')
            ->inRandomOrder()->take($nb)->get();

        $request->session()->put("challenge_questions_{$challenge->id}", $questions->pluck('id')->all());

        return view('apprenant.challenges.etape', compact('challenge', 'questions', 'deja'));
    }

    public function valider(Request $request, Challenge $challenge, GamificationService $gamification)
    {
        if ($refus = $this->verifierAcces($challenge)) {
            return $refus;
        }

        $request->validate(['reponses' => 'nullable|array']);

        $ids = $request->session()->pull("challenge_questions_{$challenge->id}");
        if (!$ids) {
            return redirect()->route('apprenant.challenges.etape', $challenge);
        }

        $reponses = $request->reponses ?? [];
        $questions = Question::with('reponses')->whereIn('id', $ids)->get();
        $total = $questions->count();
        $correct = $questions->filter(function ($q) use ($reponses) {
            $rid = $reponses[$q->id] ?? null;
            return $rid && $q->reponses->where('id', (int) $rid)->where('is_correct', true)->isNotEmpty();
        })->count();

        $score = $total > 0 ? (int) round($correct / $total * 100) : 0;
        $seuil = (int) Parametre::valeur('score_minimum_challenge', 70);
        $reussi = $score >= $seuil;
        $points = 0;

        if ($reussi) {
            $points = $gamification->capturerEtape(auth()->user(), $challenge);
        }

        $challenge->load('parcours');
        $suivante = $challenge->parcours->challenges()->where('is_active', true)
            ->where('ordre', '>', $challenge->ordre)->orderBy('ordre')->first();

        return view('apprenant.challenges.resultat', compact(
            'challenge', 'score', 'seuil', 'reussi', 'correct', 'total', 'points', 'suivante'
        ));
    }

    private function verifierAcces(Challenge $challenge)
    {
        $user = auth()->user();
        $admin = $user->role === 'admin';
        $challenge->loadMissing('parcours');

        abort_unless($admin || ($challenge->is_active && $challenge->parcours->is_active), 404);

        if (!$challenge->isJouable()) {
            return redirect()->route('apprenant.challenges.parcours', $challenge->parcours)
                ->with('error', 'Cette etape n\'est pas encore disponible.');
        }

        if (!$admin) {
            $precedentes = $challenge->parcours->challenges()->where('is_active', true)
                ->where('ordre', '<', $challenge->ordre)->pluck('id');
            $capturees = $user->flagsCaptures()->whereIn('challenge_id', $precedentes)->count();
            if ($capturees < $precedentes->count()) {
                return redirect()->route('apprenant.challenges.parcours', $challenge->parcours)
                    ->with('error', 'Capturez d\'abord le drapeau de l\'etape precedente.');
            }
        }

        return null;
    }
}
