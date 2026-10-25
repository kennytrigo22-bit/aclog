<?php

namespace App\Services;

use App\Models\Formation;
use App\Models\Parametre;
use App\Models\Progression;
use App\Models\TentativeQuiz;
use App\Models\User;

class FormationProgressService
{
    public function seuilQuiz(): int
    {
        return (int) Parametre::valeur('score_minimum_quiz', 70);
    }

    /** Ensembles precharges (2 requetes) : lecons terminees et quiz reussis de l'utilisateur. */
    public function contexte(User $user): array
    {
        return [
            'done' => Progression::where('user_id', $user->id)->where('is_completed', true)->pluck('lecon_id')->flip()->map(fn () => true)->all(),
            'quiz' => TentativeQuiz::where('user_id', $user->id)->where('is_passed', true)->pluck('quiz_id')->flip()->map(fn () => true)->all(),
        ];
    }

    public function leconsTerminees(User $user): array
    {
        return Progression::where('user_id', $user->id)->where('is_completed', true)->pluck('lecon_id')->flip()->map(fn () => true)->all();
    }

    public function status(User $user, Formation $formation, ?array $ctx = null): array
    {
        $formation->loadMissing('modules.lecons', 'modules.quiz.questions');

        $leconIds = $formation->modules->flatMap->lecons->pluck('id');
        $total = $leconIds->count();
        $doneSet = $ctx['done'] ?? $this->leconsTerminees($user);
        $done = $leconIds->filter(fn ($id) => isset($doneSet[$id]))->count();

        $nextLecon = $formation->modules->flatMap->lecons->first(fn ($l) => !isset($doneSet[$l->id]))
            ?? $formation->modules->flatMap->lecons->first();

        $quizzes = $formation->modules
            ->filter(fn($m) => $m->quiz && $m->quiz->questions->count() > 0)
            ->map(fn($m) => $m->quiz);
        if (isset($ctx['quiz'])) {
            $passedQuizIds = $quizzes->pluck('id')->filter(fn ($id) => isset($ctx['quiz'][$id]))->values();
        } else {
            $passedQuizIds = $quizzes->isEmpty() ? collect() : TentativeQuiz::where('user_id', $user->id)
                ->whereIn('quiz_id', $quizzes->pluck('id'))
                ->where('is_passed', true)
                ->pluck('quiz_id')
                ->unique();
        }
        $quizzesTotal = $quizzes->count();
        $quizzesPassed = $passedQuizIds->count();

        $leconsOk = $total > 0 && $done >= $total;
        $quizzesOk = $quizzesPassed >= $quizzesTotal;
        $isAdmin = $user->role === 'admin';

        $reason = null;
        if (!$leconsOk) {
            $reason = "Terminez toutes les lecons ({$done}/{$total})";
        } elseif (!$quizzesOk) {
            $reason = "Validez tous les quiz avec au moins {$this->seuilQuiz()}% ({$quizzesPassed}/{$quizzesTotal})";
        }

        return [
            'total' => $total,
            'done' => $done,
            'pct' => $total > 0 ? (int) round($done / $total * 100) : 0,
            'lecons_ok' => $leconsOk,
            'quizzes_total' => $quizzesTotal,
            'quizzes_passed' => $quizzesPassed,
            'passed_quiz_ids' => $passedQuizIds->all(),
            'quizzes_ok' => $quizzesOk,
            'unlocked' => $isAdmin || ($leconsOk && $quizzesOk),
            'lock_reason' => $isAdmin ? null : $reason,
            'next_lecon' => $nextLecon,
        ];
    }
}
