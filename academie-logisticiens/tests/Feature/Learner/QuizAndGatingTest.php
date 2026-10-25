<?php

namespace Tests\Feature\Learner;

use App\Models\Parametre;
use App\Models\Quiz;
use App\Models\TentativeQuiz;
use App\Services\FormationProgressService;
use Tests\TestCase;

class QuizAndGatingTest extends TestCase
{
    private function svc(): FormationProgressService
    {
        return app(FormationProgressService::class);
    }

    // ───────── Quiz ─────────

    public function test_quiz_page_renders_without_leaking_answers(): void
    {
        $fx = $this->formationComplete();
        $u = $this->apprenant();
        $this->inscrire($u, $fx['formation']);
        $res = $this->actingAs($u)->get(route('apprenant.quiz', $fx['quizzes'][0]))->assertOk();
        $this->assertStringNotContainsString('is_correct', $res->getContent());
    }

    public function test_quiz_submit_stores_attempt_and_applies_70_percent_threshold(): void
    {
        $this->seedParametres();
        $fx = $this->formationComplete();
        $u = $this->apprenant();
        $this->inscrire($u, $fx['formation']);
        $quiz = $fx['quizzes'][0]->load('questions.reponses');
        $good = $this->reponsesPour($quiz->questions);
        $bad = $this->reponsesPour($quiz->questions, false);

        // 3 questions : 2/3 = 67 % -> echec
        $ids = array_keys($good);
        $two = [$ids[0] => $good[$ids[0]], $ids[1] => $good[$ids[1]], $ids[2] => $bad[$ids[2]]];
        $this->actingAs($u)->post(route('apprenant.quiz.soumettre', $quiz), ['reponses' => $two])->assertOk();
        $t = TentativeQuiz::latest('id')->first();
        $this->assertSame(67, $t->score);
        $this->assertFalse($t->is_passed);

        // 3/3 = 100 % -> reussite
        $this->post(route('apprenant.quiz.soumettre', $quiz), ['reponses' => $good])->assertOk();
        $t = TentativeQuiz::latest('id')->first();
        $this->assertSame(100, $t->score);
        $this->assertTrue($t->is_passed);
        $this->assertSame(2, TentativeQuiz::where('user_id', $u->id)->count());
    }

    public function test_quiz_threshold_comes_from_parametre(): void
    {
        $this->seedParametres(['score_minimum_quiz' => 60]);
        $fx = $this->formationComplete();
        $u = $this->apprenant();
        $this->inscrire($u, $fx['formation']);
        $quiz = $fx['quizzes'][0]->load('questions.reponses');
        $good = $this->reponsesPour($quiz->questions);
        $bad = $this->reponsesPour($quiz->questions, false);
        $ids = array_keys($good);
        $two = [$ids[0] => $good[$ids[0]], $ids[1] => $good[$ids[1]], $ids[2] => $bad[$ids[2]]];
        $this->actingAs($u)->post(route('apprenant.quiz.soumettre', $quiz), ['reponses' => $two])->assertOk();
        $this->assertTrue(TentativeQuiz::first()->is_passed);
    }

    public function test_quiz_submit_requires_reponses_and_ignores_foreign_ids(): void
    {
        $fx = $this->formationComplete();
        $u = $this->apprenant();
        $this->inscrire($u, $fx['formation']);
        $quiz = $fx['quizzes'][0];
        $this->actingAs($u)->post(route('apprenant.quiz.soumettre', $quiz), [])->assertSessionHasErrors('reponses');

        // Reponses correctes d'un AUTRE quiz : ne comptent pas
        $other = $fx['quizzes'][1]->load('questions.reponses');
        $this->post(route('apprenant.quiz.soumettre', $quiz), ['reponses' => $this->reponsesPour($other->questions)])->assertOk();
        $this->assertSame(0, TentativeQuiz::first()->score);
    }

    public function test_quiz_with_array_answer_values_does_not_crash(): void
    {
        $fx = $this->formationComplete();
        $u = $this->apprenant();
        $this->inscrire($u, $fx['formation']);
        $q = $fx['quizzes'][0]->load('questions');
        $res = $this->actingAs($u)->post(route('apprenant.quiz.soumettre', $fx['quizzes'][0]), ['reponses' => [$q->questions[0]->id => ['x' => 1]]]);
        $this->assertLessThan(500, $res->status());
    }

    public function test_quiz_not_accessible_without_enrolment(): void
    {
        // SECURITY FINDING : ExamenController::quiz/soumettreQuiz ne verifient pas l'inscription.
        $fx = $this->formationComplete();
        $stranger = $this->apprenant();
        $quiz = $fx['quizzes'][0];
        $this->actingAs($stranger)->get(route('apprenant.quiz', $quiz))->assertForbidden();
        $this->post(route('apprenant.quiz.soumettre', $quiz), ['reponses' => [1 => 1]])->assertForbidden();
        $this->assertSame(0, TentativeQuiz::count());
    }

    public function test_quiz_not_accessible_with_pending_enrolment(): void
    {
        $fx = $this->formationComplete();
        $u = $this->apprenant();
        $this->inscrire($u, $fx['formation'], 'en_attente');
        $this->actingAs($u)->get(route('apprenant.quiz', $fx['quizzes'][0]))->assertForbidden();
    }

    // ───────── FormationProgressService ─────────

    public function test_status_locked_when_no_lessons_done(): void
    {
        $fx = $this->formationComplete();
        $u = $this->apprenant();
        $s = $this->svc()->status($u, $fx['formation']);
        $this->assertSame(4, $s['total']);
        $this->assertSame(0, $s['done']);
        $this->assertFalse($s['unlocked']);
        $this->assertStringContainsString('lecons', $s['lock_reason']);
        $this->assertSame($fx['lecons'][0]->id, $s['next_lecon']->id);
    }

    public function test_status_locked_when_lessons_done_but_a_quiz_missing(): void
    {
        $fx = $this->formationComplete();
        $u = $this->apprenant();
        $this->completerLecons($u, $fx['formation']);
        $this->reussirQuiz($u, $fx['quizzes'][0]);
        $s = $this->svc()->status($u, $fx['formation']);
        $this->assertTrue($s['lecons_ok']);
        $this->assertFalse($s['quizzes_ok']);
        $this->assertSame(2, $s['quizzes_total']);
        $this->assertSame(1, $s['quizzes_passed']);
        $this->assertFalse($s['unlocked']);
        $this->assertStringContainsString('70%', $s['lock_reason']);
    }

    public function test_status_locked_when_quiz_attempt_failed(): void
    {
        $fx = $this->formationComplete();
        $u = $this->apprenant();
        $this->completerLecons($u, $fx['formation']);
        $this->reussirQuiz($u, $fx['quizzes'][0]);
        $this->reussirQuiz($u, $fx['quizzes'][1], 60);
        $this->assertFalse($this->svc()->status($u, $fx['formation'])['unlocked']);
    }

    public function test_status_locked_when_quizzes_passed_but_lessons_incomplete(): void
    {
        $fx = $this->formationComplete();
        $u = $this->apprenant();
        foreach ($fx['quizzes'] as $q) {
            $this->reussirQuiz($u, $q);
        }
        $s = $this->svc()->status($u, $fx['formation']);
        $this->assertFalse($s['lecons_ok']);
        $this->assertTrue($s['quizzes_ok']);
        $this->assertFalse($s['unlocked']);
    }

    public function test_status_unlocked_when_all_lessons_and_all_quizzes_passed(): void
    {
        $fx = $this->formationComplete();
        $u = $this->apprenant();
        $this->debloquerExamens($u, $fx);
        $s = $this->svc()->status($u, $fx['formation']);
        $this->assertTrue($s['unlocked']);
        $this->assertNull($s['lock_reason']);
        $this->assertSame(100, $s['pct']);
    }

    public function test_quiz_with_zero_questions_does_not_block(): void
    {
        $f = $this->formation();
        $m1 = $this->module($f);
        $m2 = $this->module($f);
        $this->lecon($m1);
        $this->lecon($m2);
        $this->quiz($m1, 2);
        $this->quiz($m2, 0); // quiz vide : ignore
        $u = $this->apprenant();
        $this->completerLecons($u, $f);
        $this->reussirQuiz($u, $m1->quiz);
        $s = $this->svc()->status($u, $f->fresh());
        $this->assertSame(1, $s['quizzes_total']);
        $this->assertTrue($s['unlocked']);
    }

    public function test_formation_without_lessons_is_never_unlocked_for_learner(): void
    {
        $f = $this->formation();
        $this->assertFalse($this->svc()->status($this->apprenant(), $f)['unlocked']);
    }

    public function test_admin_bypasses_gating(): void
    {
        $fx = $this->formationComplete();
        $s = $this->svc()->status($this->admin(), $fx['formation']);
        $this->assertTrue($s['unlocked']);
        $this->assertNull($s['lock_reason']);
    }

    public function test_gestionnaire_does_not_bypass_gating(): void
    {
        $fx = $this->formationComplete();
        $this->assertFalse($this->svc()->status($this->gestionnaire(), $fx['formation'])['unlocked']);
    }

    public function test_progression_of_other_users_does_not_count(): void
    {
        $fx = $this->formationComplete();
        $a = $this->apprenant();
        $b = $this->apprenant();
        $this->debloquerExamens($a, $fx);
        $this->assertFalse($this->svc()->status($b, $fx['formation'])['unlocked']);
    }

    public function test_seuil_quiz_reads_parametre(): void
    {
        $this->assertSame(70, $this->svc()->seuilQuiz());
        Parametre::definir('score_minimum_quiz', '85');
        $this->assertSame(85, $this->svc()->seuilQuiz());
    }

    public function test_unused_quiz_model_binding_404(): void
    {
        $this->actingAs($this->apprenant())->get('/espace/quiz/999999')->assertNotFound();
        $this->assertSame(0, Quiz::count());
    }
}
