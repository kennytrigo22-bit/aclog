<?php

namespace Tests\Feature\Learner;

use App\Models\Progression;
use Tests\TestCase;

class CoursTest extends TestCase
{
    public function test_dashboard_shows_progress_cards_with_link_to_next_lesson(): void
    {
        $fx = $this->formationComplete(['titre' => 'Formation Dash']);
        $u = $this->apprenant();
        $insc = $this->inscrire($u, $fx['formation']);
        // 1ere lecon terminee -> la prochaine est la 2e
        Progression::create(['user_id' => $u->id, 'lecon_id' => $fx['lecons'][0]->id, 'is_completed' => true, 'completed_at' => now()]);

        $url = route('apprenant.cours.lecon', [$insc, $fx['lecons'][1]]);
        $this->actingAs($u)->get('/espace/dashboard')->assertOk()->assertSee('Formation Dash')->assertSee($url, false);
    }

    public function test_dashboard_only_shows_active_or_finished_inscriptions(): void
    {
        $u = $this->apprenant();
        $a = $this->formation(['titre' => 'Active Formation']);
        $b = $this->formation(['titre' => 'Attente Formation']);
        $this->module($a); $this->module($b);
        $this->inscrire($u, $a);
        $this->inscrire($u, $b, 'en_attente');
        $this->actingAs($u)->get('/espace/dashboard')->assertOk()->assertSee('Active Formation')->assertDontSee('Attente Formation');
    }

    public function test_dashboard_with_no_inscription_renders(): void
    {
        $this->actingAs($this->apprenant())->get('/espace/dashboard')->assertOk();
    }

    public function test_admin_can_open_learner_dashboard_and_gets_inscriptions_for_all_formations(): void
    {
        $f = $this->formationComplete();
        $admin = $this->admin();
        $this->actingAs($admin)->get('/espace/dashboard')->assertOk();
        $this->assertDatabaseHas('inscriptions', ['user_id' => $admin->id, 'formation_id' => $f['formation']->id, 'statut' => 'active']);
    }

    public function test_gestionnaire_cannot_access_learner_area(): void
    {
        $this->actingAs($this->gestionnaire())->get('/espace/dashboard')->assertForbidden();
        $this->get('/espace/mes-formations')->assertForbidden();
    }

    public function test_mes_formations_lists_own_inscriptions_only(): void
    {
        $u = $this->apprenant();
        $other = $this->apprenant();
        $mine = $this->formation(['titre' => 'Ma Formation Perso']);
        $theirs = $this->formation(['titre' => 'Formation Autrui']);
        $this->inscrire($u, $mine);
        $this->inscrire($other, $theirs);
        $this->actingAs($u)->get('/espace/mes-formations')->assertOk()->assertSee('Ma Formation Perso')->assertDontSee('Formation Autrui');
    }

    public function test_ping_returns_no_content(): void
    {
        $this->actingAs($this->apprenant())->get('/espace/ping')->assertNoContent();
    }

    // ───────── Lecon access / IDOR ─────────

    public function test_owner_with_active_or_finished_inscription_can_open_lesson(): void
    {
        $fx = $this->formationComplete();
        $u = $this->apprenant();
        $insc = $this->inscrire($u, $fx['formation']);
        $this->actingAs($u)->get(route('apprenant.cours.lecon', [$insc, $fx['lecons'][0]]))->assertOk()->assertSee($fx['lecons'][0]->titre);
        $insc->update(['statut' => 'terminee']);
        $this->get(route('apprenant.cours.lecon', [$insc, $fx['lecons'][0]]))->assertOk();
    }

    public function test_pending_inscription_cannot_open_lesson(): void
    {
        $fx = $this->formationComplete();
        $u = $this->apprenant();
        $insc = $this->inscrire($u, $fx['formation'], 'en_attente');
        $this->actingAs($u)->get(route('apprenant.cours.lecon', [$insc, $fx['lecons'][0]]))->assertForbidden();
    }

    public function test_other_users_inscription_is_forbidden(): void
    {
        $fx = $this->formationComplete();
        $owner = $this->apprenant();
        $intruder = $this->apprenant();
        $insc = $this->inscrire($owner, $fx['formation']);
        $this->actingAs($intruder)->get(route('apprenant.cours.lecon', [$insc, $fx['lecons'][0]]))->assertForbidden();
    }

    public function test_lesson_must_belong_to_inscription_formation(): void
    {
        $fx = $this->formationComplete();
        $other = $this->formationComplete();
        $u = $this->apprenant();
        $insc = $this->inscrire($u, $fx['formation']);
        $this->actingAs($u)->get(route('apprenant.cours.lecon', [$insc, $other['lecons'][0]]))->assertNotFound();
    }

    public function test_admin_bypasses_lesson_ownership(): void
    {
        $fx = $this->formationComplete();
        $owner = $this->apprenant();
        $insc = $this->inscrire($owner, $fx['formation'], 'en_attente');
        $this->actingAs($this->admin())->get(route('apprenant.cours.lecon', [$insc, $fx['lecons'][0]]))->assertOk();
    }

    public function test_lesson_unknown_ids_404_and_guest_redirected(): void
    {
        $this->get('/espace/cours/1/lecon/1')->assertRedirect(route('login'));
        $this->actingAs($this->apprenant())->get('/espace/cours/99999/lecon/99999')->assertNotFound();
    }

    // ───────── Progression ─────────

    public function test_progression_save_for_enrolled_learner(): void
    {
        $fx = $this->formationComplete();
        $u = $this->apprenant();
        $this->inscrire($u, $fx['formation']);
        $lecon = $fx['lecons'][0];

        $this->actingAs($u)->postJson('/espace/progression', ['lecon_id' => $lecon->id, 'position_video' => 42, 'is_completed' => true])
            ->assertOk()->assertJsonPath('success', true);
        $this->assertDatabaseHas('progressions', ['user_id' => $u->id, 'lecon_id' => $lecon->id, 'position_video' => 42, 'is_completed' => 1]);

        // Idempotent (updateOrCreate) : pas de doublon
        $this->postJson('/espace/progression', ['lecon_id' => $lecon->id, 'position_video' => 50, 'is_completed' => false])->assertOk();
        $this->assertSame(1, Progression::where('user_id', $u->id)->where('lecon_id', $lecon->id)->count());
    }

    public function test_progression_save_forbidden_for_non_enrolled_or_pending(): void
    {
        $fx = $this->formationComplete();
        $u = $this->apprenant();
        $lecon = $fx['lecons'][0];
        $this->actingAs($u)->postJson('/espace/progression', ['lecon_id' => $lecon->id, 'is_completed' => true])->assertForbidden();
        $this->inscrire($u, $fx['formation'], 'en_attente');
        $this->postJson('/espace/progression', ['lecon_id' => $lecon->id, 'is_completed' => true])->assertForbidden();
        $this->assertSame(0, Progression::count());
    }

    public function test_progression_validation(): void
    {
        $u = $this->apprenant();
        $this->actingAs($u)->postJson('/espace/progression', [])->assertStatus(422);
        $this->postJson('/espace/progression', ['lecon_id' => 999999])->assertStatus(422);
        $fx = $this->formationComplete();
        $this->inscrire($u, $fx['formation']);
        $this->postJson('/espace/progression', ['lecon_id' => $fx['lecons'][0]->id, 'position_video' => -5])->assertStatus(422);
    }

    public function test_progression_save_requires_login(): void
    {
        $this->postJson('/espace/progression', ['lecon_id' => 1])->assertUnauthorized();
    }
}
