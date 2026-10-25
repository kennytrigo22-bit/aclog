<?php

namespace Tests\Feature\Learner;

use App\Models\FlagCapture;
use App\Models\User;
use Illuminate\Database\QueryException;
use Tests\TestCase;

class ChallengeTest extends TestCase
{
    private User $u;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedParametres();
        $this->u = $this->apprenant();
    }

    private function jouer($challenge, bool $correct = true, ?array $subset = null)
    {
        $this->actingAs($this->u)->get(route('apprenant.challenges.etape', $challenge))->assertOk();
        $ids = session("challenge_questions_{$challenge->id}");
        $questions = $challenge->questions()->with('reponses')->whereIn('id', $ids)->get();
        $rep = $this->reponsesPour($questions, $correct);
        if ($subset !== null) {
            $rep = array_slice($rep, 0, $subset['n'], true);
        }

        return $this->post(route('apprenant.challenges.valider', $challenge), ['reponses' => $rep]);
    }

    public function test_index_lists_active_parcours_only(): void
    {
        $this->parcours(['titre' => 'Parcours Actif Visible']);
        $this->parcours(['titre' => 'Parcours Inactif Cache', 'is_active' => false]);
        $this->actingAs($this->u)->get('/espace/challenges')->assertOk()->assertSee('Parcours Actif Visible')->assertDontSee('Parcours Inactif Cache');
    }

    public function test_parcours_page_states_locked_until_previous_captured(): void
    {
        $p = $this->parcours();
        $c1 = $this->challenge($p, 1);
        $c2 = $this->challenge($p, 2);
        $res = $this->actingAs($this->u)->get(route('apprenant.challenges.parcours', $p))->assertOk();
        $etapes = $res->viewData('etapes');
        $this->assertTrue($etapes[0]->debloquee);
        $this->assertFalse($etapes[1]->debloquee);

        FlagCapture::create(['user_id' => $this->u->id, 'challenge_id' => $c1->id, 'flag_soumis' => 'x', 'captured_at' => now()]);
        $etapes = $this->get(route('apprenant.challenges.parcours', $p))->viewData('etapes');
        $this->assertTrue($etapes[0]->capturee);
        $this->assertTrue($etapes[1]->debloquee);
    }

    public function test_step_with_fewer_than_ten_questions_is_not_playable(): void
    {
        $p = $this->parcours();
        $c = $this->challenge($p, 1, 9);
        $res = $this->actingAs($this->u)->get(route('apprenant.challenges.parcours', $p));
        $this->assertFalse($res->viewData('etapes')[0]->jouable);
        $this->get(route('apprenant.challenges.etape', $c))->assertRedirect(route('apprenant.challenges.parcours', $p))->assertSessionHas('error');
        $this->post(route('apprenant.challenges.valider', $c), ['reponses' => []])->assertRedirect(route('apprenant.challenges.parcours', $p));
        $this->assertSame(0, FlagCapture::count());
    }

    public function test_locked_step_redirects_to_parcours(): void
    {
        $p = $this->parcours();
        $this->challenge($p, 1);
        $c2 = $this->challenge($p, 2);
        $this->actingAs($this->u)->get(route('apprenant.challenges.etape', $c2))->assertRedirect(route('apprenant.challenges.parcours', $p))->assertSessionHas('error');
        $this->post(route('apprenant.challenges.valider', $c2), ['reponses' => []])->assertRedirect(route('apprenant.challenges.parcours', $p));
    }

    public function test_admin_can_play_locked_step(): void
    {
        $p = $this->parcours();
        $this->challenge($p, 1);
        $c2 = $this->challenge($p, 2);
        $this->actingAs($this->admin())->get(route('apprenant.challenges.etape', $c2))->assertOk();
    }

    public function test_inactive_parcours_or_step_is_404_for_learner(): void
    {
        $p = $this->parcours(['is_active' => false]);
        $c = $this->challenge($p, 1);
        $this->actingAs($this->u)->get(route('apprenant.challenges.parcours', $p))->assertNotFound();
        $this->get(route('apprenant.challenges.etape', $c))->assertNotFound();

        $p2 = $this->parcours();
        $c2 = $this->challenge($p2, 1, 10, ['is_active' => false]);
        $this->get(route('apprenant.challenges.etape', $c2))->assertNotFound();
    }

    public function test_step_serves_at_least_ten_random_questions_without_leaking_answers(): void
    {
        $p = $this->parcours();
        $c = $this->challenge($p, 1, 15, ['nb_questions' => 10]);
        $res = $this->actingAs($this->u)->get(route('apprenant.challenges.etape', $c))->assertOk();
        $this->assertCount(10, session("challenge_questions_{$c->id}"));
        $this->assertStringNotContainsString('is_correct', $res->getContent());

        // nb_questions < 10 en base -> plancher a 10
        $c2 = $this->challenge($p, 2, 12, ['nb_questions' => 3]);
        FlagCapture::create(['user_id' => $this->u->id, 'challenge_id' => $c->id, 'flag_soumis' => 'x', 'captured_at' => now()]);
        $this->get(route('apprenant.challenges.etape', $c2))->assertOk();
        $this->assertCount(10, session("challenge_questions_{$c2->id}"));
    }

    public function test_capture_with_all_correct_awards_points_and_flag_once(): void
    {
        $p = $this->parcours();
        $c = $this->challenge($p, 1, 10, ['points' => 5]);
        $this->jouer($c)->assertOk();

        $this->assertDatabaseHas('flags_captures', ['user_id' => $this->u->id, 'challenge_id' => $c->id]);
        $this->assertSame(5, $this->u->fresh()->points);
        $this->assertDatabaseHas('notifications_internes', ['user_id' => $this->u->id, 'type' => 'points_gagnes']);

        // Rejouer : pas de nouveaux points
        $this->jouer($c)->assertOk();
        $this->assertSame(5, $this->u->fresh()->points);
        $this->assertSame(1, FlagCapture::count());
    }

    public function test_below_threshold_does_not_capture(): void
    {
        $p = $this->parcours();
        $c = $this->challenge($p, 1);
        $this->jouer($c, false)->assertOk();
        $this->assertSame(0, FlagCapture::count());
        $this->assertSame(0, $this->u->fresh()->points);
    }

    public function test_threshold_exact_70_percent_passes_and_60_fails(): void
    {
        $p = $this->parcours();
        $c = $this->challenge($p, 1);
        $this->jouer($c, true, ['n' => 6])->assertOk();    // 6/10
        $this->assertSame(0, FlagCapture::count());
        $this->jouer($c, true, ['n' => 7])->assertOk();    // 7/10
        $this->assertSame(1, FlagCapture::count());
    }

    public function test_threshold_comes_from_parametre(): void
    {
        $this->seedParametres(['score_minimum_challenge' => 50]);
        $p = $this->parcours();
        $c = $this->challenge($p, 1);
        $this->jouer($c, true, ['n' => 5])->assertOk();
        $this->assertSame(1, FlagCapture::count());
    }

    public function test_validation_without_loading_step_redirects_and_session_is_single_use(): void
    {
        $p = $this->parcours();
        $c = $this->challenge($p, 1);
        $this->actingAs($this->u)->post(route('apprenant.challenges.valider', $c), ['reponses' => []])->assertRedirect(route('apprenant.challenges.etape', $c));

        $this->jouer($c, false);
        // meme session rejouee : les ids ont ete consommes
        $this->post(route('apprenant.challenges.valider', $c), ['reponses' => []])->assertRedirect(route('apprenant.challenges.etape', $c));
    }

    public function test_cannot_answer_with_questions_outside_the_draw(): void
    {
        $p = $this->parcours();
        $c = $this->challenge($p, 1, 15, ['nb_questions' => 10]);
        $this->actingAs($this->u)->get(route('apprenant.challenges.etape', $c));
        $served = session("challenge_questions_{$c->id}");
        $others = $c->questions()->with('reponses')->whereNotIn('id', $served)->get();
        $this->post(route('apprenant.challenges.valider', $c), ['reponses' => $this->reponsesPour($others)])->assertOk();
        $this->assertSame(0, FlagCapture::count());
    }

    public function test_points_convert_to_jetons_at_threshold(): void
    {
        $this->u->forceFill(['points' => 98])->save();
        $p = $this->parcours();
        $c = $this->challenge($p, 1, 10, ['points' => 5]);
        $this->jouer($c)->assertOk();
        $u = $this->u->fresh();
        $this->assertSame(3, $u->points);
        $this->assertSame(5000, $u->jetons);
        $this->assertDatabaseHas('notifications_internes', ['user_id' => $u->id, 'type' => 'jetons_credites']);
    }

    public function test_flag_capture_is_unique_per_user_and_challenge(): void
    {
        $c = $this->challenge($this->parcours(), 1);
        FlagCapture::create(['user_id' => $this->u->id, 'challenge_id' => $c->id, 'flag_soumis' => 'a', 'captured_at' => now()]);
        $this->expectException(QueryException::class);
        FlagCapture::create(['user_id' => $this->u->id, 'challenge_id' => $c->id, 'flag_soumis' => 'b', 'captured_at' => now()]);
    }

    public function test_second_step_unlocks_after_first_capture_and_two_users_are_independent(): void
    {
        $p = $this->parcours();
        $c1 = $this->challenge($p, 1);
        $c2 = $this->challenge($p, 2);
        $this->jouer($c1)->assertOk();
        $this->get(route('apprenant.challenges.etape', $c2))->assertOk();

        $other = $this->apprenant();
        $this->actingAs($other)->get(route('apprenant.challenges.etape', $c2))->assertRedirect(route('apprenant.challenges.parcours', $p));
    }

    public function test_validation_endpoint_is_throttled_at_30_per_minute(): void
    {
        $p = $this->parcours();
        $c = $this->challenge($p, 1);
        $this->actingAs($this->u);
        $last = 0;
        for ($i = 1; $i <= 31; $i++) {
            $last = $this->post(route('apprenant.challenges.valider', $c), ['reponses' => []])->status();
            if ($last === 429) {
                $this->assertSame(31, $i, "Throttle declenche des la requete {$i} au lieu de 31 (limite 30/min). Cause: throttle numerique global 240,1 partage la meme cle.");
                return;
            }
        }
        $this->fail('Aucun 429 apres 31 requetes.');
    }

    public function test_guest_cannot_access_challenges(): void
    {
        $p = $this->parcours();
        $c = $this->challenge($p, 1);
        $this->get('/espace/challenges')->assertRedirect(route('login'));
        $this->get(route('apprenant.challenges.etape', $c))->assertRedirect(route('login'));
        $this->post(route('apprenant.challenges.valider', $c))->assertRedirect(route('login'));
    }
}
