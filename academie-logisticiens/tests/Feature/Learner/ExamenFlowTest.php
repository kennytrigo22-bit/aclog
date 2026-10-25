<?php

namespace Tests\Feature\Learner;

use App\Models\Certificat;
use App\Models\Inscription;
use App\Models\TentativeExamen;
use App\Models\User;
use App\Services\CertificatService;
use Tests\TestCase;

class ExamenFlowTest extends TestCase
{
    private array $fx;
    private User $u;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedParametres();
        $this->fx = $this->formationComplete();
        $this->u = $this->apprenant();
        $this->inscrire($this->u, $this->fx['formation']);
    }

    private function unlock(): void
    {
        $this->debloquerExamens($this->u, $this->fx);
    }

    private function demarrer($examen)
    {
        return $this->actingAs($this->u)->post(route('apprenant.examen.demarrer', $examen));
    }

    private function servedIds($examen): array
    {
        return session("examen_questions_{$examen->id}_{$this->u->id}") ?? [];
    }

    // ───────── Gating ─────────

    public function test_exam_is_locked_until_lessons_and_quizzes_done(): void
    {
        $final = $this->fx['final'];
        $this->actingAs($this->u)->get(route('apprenant.examen', $final))->assertRedirect(route('apprenant.cours.index'))->assertSessionHas('error');
        $this->post(route('apprenant.examen.demarrer', $final))->assertRedirect(route('apprenant.cours.index'));
        $this->get(route('apprenant.examen.passer', $final))->assertRedirect(route('apprenant.cours.index'));
        $this->post(route('apprenant.examen.soumettre', $final), ['reponses' => []])->assertRedirect(route('apprenant.cours.index'));
        $this->assertSame(0, TentativeExamen::count());

        $this->completerLecons($this->u, $this->fx['formation']);
        $this->get(route('apprenant.examen', $final))->assertRedirect(route('apprenant.cours.index'));

        $this->reussirQuiz($this->u, $this->fx['quizzes'][0]);
        $this->reussirQuiz($this->u, $this->fx['quizzes'][1]);
        $this->get(route('apprenant.examen', $final))->assertOk();
    }

    public function test_admin_can_open_exam_without_progress(): void
    {
        $this->actingAs($this->admin())->get(route('apprenant.examen', $this->fx['final']))->assertOk();
    }

    public function test_consignes_page_renders_for_test_and_final(): void
    {
        $this->unlock();
        $this->actingAs($this->u)->get(route('apprenant.examen', $this->fx['final']))->assertOk();
        $this->get(route('apprenant.examen', $this->fx['test']))->assertOk();
    }

    // ───────── Demarrer / passer ─────────

    public function test_demarrer_redirects_to_passer_and_stores_session_state(): void
    {
        $this->unlock();
        $final = $this->fx['final'];
        $this->demarrer($final)->assertRedirect(route('apprenant.examen.passer', $final));
        $ids = $this->servedIds($final);
        $this->assertCount(5, $ids);
        $this->assertNotNull(session("examen_start_{$final->id}_{$this->u->id}"));
    }

    public function test_demarrer_resumes_running_attempt_with_same_questions(): void
    {
        $this->unlock();
        $final = $this->fx['final'];
        $this->addQuestions($final, 10); // 15 en pool, 5 tires
        $this->demarrer($final);
        $first = $this->servedIds($final);
        $start = session("examen_start_{$final->id}_{$this->u->id}");
        $this->demarrer($final)->assertRedirect(route('apprenant.examen.passer', $final));
        $this->assertSame($first, $this->servedIds($final));
        $this->assertSame($start, session("examen_start_{$final->id}_{$this->u->id}"));
    }

    public function test_demarrer_with_empty_pool_redirects_with_error(): void
    {
        $f = $this->formationComplete();
        $f['final']->questions()->get()->each->delete();
        $this->actingAs($this->admin())->post(route('apprenant.examen.demarrer', $f['final']))->assertRedirect(route('apprenant.cours.index'))->assertSessionHas('error');
    }

    public function test_passer_without_start_redirects_to_consignes(): void
    {
        $this->unlock();
        $this->actingAs($this->u)->get(route('apprenant.examen.passer', $this->fx['final']))->assertRedirect(route('apprenant.examen', $this->fx['final']));
    }

    public function test_passer_page_shows_questions_but_never_leaks_correct_answers(): void
    {
        $this->unlock();
        $final = $this->fx['final'];
        $this->demarrer($final);
        $res = $this->get(route('apprenant.examen.passer', $final))->assertOk();
        $html = $res->getContent();
        $this->assertStringNotContainsString('is_correct', $html);
        $this->assertStringNotContainsString('"correct"', $html);
        $q = $final->questions()->first();
        $this->assertStringContainsString(e($q->texte), $html);
    }

    // ───────── Soumettre ─────────

    public function test_submit_all_correct_on_final_creates_certificate_and_finishes_inscription(): void
    {
        $this->unlock();
        $final = $this->fx['final'];
        $this->demarrer($final);
        $reponses = $this->reponsesPour($final->questions()->with('reponses')->get());

        $this->post(route('apprenant.examen.soumettre', $final), ['reponses' => $reponses])->assertOk();

        $t = TentativeExamen::first();
        $this->assertSame(100, $t->score);
        $this->assertTrue($t->is_passed);
        $this->assertSame(1, $t->numero_tentative);
        $c = Certificat::where('user_id', $this->u->id)->first();
        $this->assertNotNull($c);
        $this->assertSame(100, $c->score_obtenu);
        $this->assertMatchesRegularExpression('/^CFF-' . now()->year . '-ACLOG-CG-001CG$/', $c->numero);
        $this->assertMatchesRegularExpression('/^[A-Z0-9]{8}-[A-Z0-9]{8}$/', $c->code_verification);
        $this->assertSame('terminee', Inscription::where('user_id', $this->u->id)->first()->statut);
        $this->assertDatabaseHas('notifications_internes', ['user_id' => $this->u->id, 'type' => 'certificat_obtenu']);
        // session d'examen purgee
        $this->assertNull(session("examen_start_{$final->id}_{$this->u->id}"));
    }

    public function test_unanswered_questions_count_as_wrong_over_all_served_questions(): void
    {
        $this->unlock();
        $final = $this->fx['final'];
        $this->demarrer($final);
        $all = $this->reponsesPour($final->questions()->with('reponses')->get());
        $three = array_slice($all, 0, 3, true); // 3/5 = 60 %
        $this->post(route('apprenant.examen.soumettre', $final), ['reponses' => $three])->assertOk();
        $t = TentativeExamen::first();
        $this->assertSame(60, $t->score);
        $this->assertFalse($t->is_passed);
        $this->assertSame(0, Certificat::count());
    }

    public function test_four_of_five_correct_passes(): void
    {
        $this->unlock();
        $final = $this->fx['final'];
        $this->demarrer($final);
        $all = $this->reponsesPour($final->questions()->with('reponses')->get());
        $this->post(route('apprenant.examen.soumettre', $final), ['reponses' => array_slice($all, 0, 4, true)])->assertOk();
        $this->assertSame(80, TentativeExamen::first()->score);
        $this->assertSame(1, Certificat::count());
    }

    public function test_answers_for_non_served_questions_are_ignored(): void
    {
        $this->unlock();
        $final = $this->fx['final'];
        $this->addQuestions($final, 10);
        $this->demarrer($final);
        $served = $this->servedIds($final);
        $notServed = $final->questions()->whereNotIn('id', $served)->with('reponses')->get();
        $answers = $this->reponsesPour($notServed); // toutes correctes mais hors tirage
        $this->post(route('apprenant.examen.soumettre', $final), ['reponses' => $answers])->assertOk();
        $this->assertSame(0, TentativeExamen::first()->score);
        $this->assertSame(0, Certificat::count());
    }

    public function test_submit_without_started_session_is_rejected(): void
    {
        $this->unlock();
        $final = $this->fx['final'];
        $all = $this->reponsesPour($final->questions()->with('reponses')->get());
        $this->actingAs($this->u)->post(route('apprenant.examen.soumettre', $final), ['reponses' => $all])
            ->assertRedirect(route('apprenant.examen', $final))->assertSessionHas('error');
        $this->assertSame(0, TentativeExamen::count());
        $this->assertSame(0, Certificat::count());
    }

    public function test_cannot_replay_same_session_twice(): void
    {
        $this->unlock();
        $final = $this->fx['final'];
        $this->demarrer($final);
        $all = $this->reponsesPour($final->questions()->with('reponses')->get());
        $this->post(route('apprenant.examen.soumettre', $final), ['reponses' => $all])->assertOk();
        $this->post(route('apprenant.examen.soumettre', $final), ['reponses' => $all])->assertRedirect();
        $this->assertSame(1, TentativeExamen::count());
        $this->assertSame(1, Certificat::count());
    }

    public function test_time_expired_is_detected_and_displayed(): void
    {
        $this->unlock();
        $final = $this->fx['final'];
        $this->demarrer($final);
        $this->travel(61)->minutes();
        $res = $this->post(route('apprenant.examen.soumettre', $final), ['reponses' => []])->assertOk();
        $this->assertStringContainsString('temps', mb_strtolower($res->getContent()));
    }

    public function test_late_submission_cannot_earn_a_certificate(): void
    {
        // FINDING : tempsEcoule n'est utilise que pour l'affichage ; une soumission tardive (timer client contourne)
        // avec toutes les bonnes reponses obtient quand meme le certificat (ExamenController::soumettreExamen).
        $this->unlock();
        $final = $this->fx['final'];
        $this->demarrer($final);
        $all = $this->reponsesPour($final->questions()->with('reponses')->get());
        $this->travel(5)->hours();
        $this->post(route('apprenant.examen.soumettre', $final), ['reponses' => $all]);
        $this->assertSame(0, Certificat::count(), 'Soumission 5h apres un examen de 60 min : certificat delivre.');
    }

    public function test_final_exam_max_attempts_enforced(): void
    {
        $this->unlock();
        $final = $this->fx['final']; // nb_tentatives_max = 1
        $this->demarrer($final);
        $this->post(route('apprenant.examen.soumettre', $final), ['reponses' => []])->assertOk();
        $this->assertFalse(TentativeExamen::first()->is_passed);

        $this->get(route('apprenant.examen', $final))->assertRedirect(route('apprenant.cours.index'))->assertSessionHas('error');
        $this->post(route('apprenant.examen.demarrer', $final))->assertRedirect(route('apprenant.cours.index'));
        $this->assertSame(1, TentativeExamen::count());
    }

    public function test_final_exam_allows_second_attempt_when_max_is_two_and_numbers_attempts(): void
    {
        $this->unlock();
        $final = $this->fx['final'];
        $final->update(['nb_tentatives_max' => 2]);
        for ($i = 1; $i <= 2; $i++) {
            $this->demarrer($final)->assertRedirect(route('apprenant.examen.passer', $final));
            $this->post(route('apprenant.examen.soumettre', $final), ['reponses' => []])->assertOk();
        }
        $this->assertSame([1, 2], TentativeExamen::orderBy('id')->pluck('numero_tentative')->all());
        $this->post(route('apprenant.examen.demarrer', $final))->assertRedirect(route('apprenant.cours.index'));
    }

    public function test_admin_reactivation_frees_attempts(): void
    {
        $this->unlock();
        $final = $this->fx['final'];
        $this->demarrer($final);
        $this->post(route('apprenant.examen.soumettre', $final), ['reponses' => []]);
        $t = TentativeExamen::first();

        $this->actingAs($this->admin())->post(route('admin.examens.tentatives.reactiver', $t))->assertRedirect();
        $this->assertFalse($t->fresh()->is_active);

        $this->actingAs($this->u)->post(route('apprenant.examen.demarrer', $final))->assertRedirect(route('apprenant.examen.passer', $final));
    }

    public function test_test_exam_never_creates_certificate_and_has_no_attempt_limit(): void
    {
        $this->unlock();
        $test = $this->fx['test']; // nb_tentatives_max 9999
        $test->update(['nb_tentatives_max' => 1]); // meme a 1, un examen "test" n'est pas limite
        for ($i = 0; $i < 2; $i++) {
            $this->demarrer($test)->assertRedirect(route('apprenant.examen.passer', $test));
            $all = $this->reponsesPour($test->questions()->with('reponses')->get());
            $this->post(route('apprenant.examen.soumettre', $test), ['reponses' => $all])->assertOk();
        }
        $this->assertSame(2, TentativeExamen::count());
        $this->assertSame(0, Certificat::count());
        $this->assertSame('active', Inscription::first()->statut);
    }

    public function test_exam_of_another_formation_requires_that_formation_progress(): void
    {
        $other = $this->formationComplete();
        $this->unlock();
        $this->actingAs($this->u)->get(route('apprenant.examen', $other['final']))->assertRedirect(route('apprenant.cours.index'));
    }

    public function test_guest_cannot_access_exam_routes(): void
    {
        auth()->logout();
        $this->get(route('apprenant.examen', $this->fx['final']))->assertRedirect(route('login'));
        $this->post(route('apprenant.examen.soumettre', $this->fx['final']))->assertRedirect(route('login'));
    }

    // ───────── Numero de certificat ─────────

    public function test_certificate_number_uses_country_code_and_sequence_per_year(): void
    {
        $svc = app(CertificatService::class);
        $f = $this->fx['formation'];
        $sn = $this->apprenant(['pays' => 'Sénégal']);
        $cg = $this->apprenant(['pays' => 'Congo']);
        $xx = $this->apprenant(['pays' => 'Pays Inconnu']);
        $y = now()->year;

        $this->assertSame("CFF-{$y}-ACLOG-CG-001SN", $svc->generer($sn, $f, 90)->numero);
        $this->assertSame("CFF-{$y}-ACLOG-CG-002CG", $svc->generer($cg, $f, 90)->numero);
        $this->assertSame("CFF-{$y}-ACLOG-CG-003XX", $svc->generer($xx, $f, 90)->numero);
    }

    public function test_certificate_sequence_restarts_each_year(): void
    {
        $svc = app(CertificatService::class);
        $f = $this->fx['formation'];
        $old = $this->certificat($this->apprenant(), $f, ['issued_at' => now()->subYear(), 'numero' => 'CFF-' . now()->subYear()->year . '-ACLOG-CG-001CG']);
        $c = $svc->generer($this->apprenant(['pays' => 'Congo']), $f, 80);
        $this->assertStringEndsWith('-001CG', $c->numero);
    }

    public function test_certificate_generation_survives_deleted_certificates(): void
    {
        // FINDING : sequence = count()+1 ; apres suppression d'un certificat (ex. compte supprime), le numero peut
        // entrer en collision avec un numero existant (contrainte unique) -> 500.
        $svc = app(CertificatService::class);
        $f = $this->fx['formation'];
        $users = [];
        for ($i = 0; $i < 3; $i++) {
            $users[$i] = $this->apprenant(['pays' => 'Congo']);
            $svc->generer($users[$i], $f, 80);
        }
        $users[0]->delete(); // cascade -> supprime le certificat 001
        $new = $this->apprenant(['pays' => 'Congo']);
        $c = $svc->generer($new, $f, 80);
        $this->assertSame(3, Certificat::count());
        $this->assertSame(3, Certificat::distinct('numero')->count('numero'));
        $this->assertNotNull($c->id);
    }
}
