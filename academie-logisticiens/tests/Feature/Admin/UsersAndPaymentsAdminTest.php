<?php

namespace Tests\Feature\Admin;

use App\Models\Inscription;
use App\Models\Paiement;
use App\Models\User;
use App\Notifications\PaiementRejete;
use App\Notifications\PaiementValide;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UsersAndPaymentsAdminTest extends TestCase
{
    // ───────── Utilisateurs (admin) ─────────

    public function test_admin_lists_filters_and_searches_users(): void
    {
        $this->actingAs($this->admin());
        $this->apprenant(['name' => 'Alpha', 'email' => 'alpha@example.test']);
        $this->gestionnaire(['name' => 'Beta', 'email' => 'beta@example.test']);
        $this->get(route('admin.utilisateurs.index'))->assertOk()->assertSee('alpha@example.test')->assertSee('beta@example.test');
        $this->get(route('admin.utilisateurs.index', ['role' => 'gestionnaire']))->assertOk()->assertSee('beta@example.test')->assertDontSee('alpha@example.test');
        $this->get(route('admin.utilisateurs.index', ['q' => 'alpha']))->assertOk()->assertSee('alpha@example.test')->assertDontSee('beta@example.test');
    }

    public function test_user_search_handles_sqli_and_wildcards(): void
    {
        $this->actingAs($this->admin());
        foreach (["' OR 1=1 --", "%", "\\", "'; DROP TABLE users; --"] as $q) {
            $this->get(route('admin.utilisateurs.index', ['q' => $q]))->assertOk();
        }
        $this->assertGreaterThan(0, User::count());
    }

    public function test_admin_updates_user_role_points_and_jetons(): void
    {
        $this->actingAs($this->admin());
        $u = $this->apprenant();
        $this->get(route('admin.utilisateurs.edit', $u))->assertOk();
        $this->put(route('admin.utilisateurs.update', $u), [
            'name' => 'Nouveau', 'prenom' => 'P', 'email' => 'new@example.test', 'role' => 'gestionnaire', 'points' => 10, 'jetons' => 2000,
        ])->assertRedirect(route('admin.utilisateurs.edit', $u));
        $u->refresh();
        $this->assertSame('gestionnaire', $u->role);
        $this->assertSame(10, $u->points);
        $this->assertSame(2000, $u->jetons);
    }

    public function test_user_update_validation(): void
    {
        $this->actingAs($this->admin());
        $u = $this->apprenant();
        $other = $this->apprenant();
        $base = ['name' => 'N', 'email' => $u->email, 'role' => 'apprenant'];
        $this->put(route('admin.utilisateurs.update', $u), array_merge($base, ['role' => 'superadmin']))->assertSessionHasErrors('role');
        $this->put(route('admin.utilisateurs.update', $u), array_merge($base, ['email' => $other->email]))->assertSessionHasErrors('email');
        $this->put(route('admin.utilisateurs.update', $u), array_merge($base, ['points' => -1]))->assertSessionHasErrors('points');
        $this->put(route('admin.utilisateurs.update', $u), array_merge($base, ['jetons' => 'abc']))->assertSessionHasErrors('jetons');
    }

    public function test_admin_cannot_change_own_role(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->put(route('admin.utilisateurs.update', $admin), ['name' => 'X', 'email' => $admin->email, 'role' => 'apprenant']);
        $this->assertSame('admin', $admin->fresh()->role);
    }

    public function test_admin_resets_password(): void
    {
        $this->actingAs($this->admin());
        $u = $this->apprenant();
        $this->post(route('admin.utilisateurs.resetPassword', $u), ['new_password' => 'short', 'new_password_confirmation' => 'short'])->assertSessionHasErrors('new_password');
        $this->post(route('admin.utilisateurs.resetPassword', $u), ['new_password' => 'Nouveau-Pass-123', 'new_password_confirmation' => 'autre'])->assertSessionHasErrors('new_password');
        $this->post(route('admin.utilisateurs.resetPassword', $u), ['new_password' => 'Nouveau-Pass-123', 'new_password_confirmation' => 'Nouveau-Pass-123'])->assertSessionHas('success');
        $this->assertTrue(Hash::check('Nouveau-Pass-123', $u->fresh()->password));
    }

    public function test_admin_suspends_and_unsuspends(): void
    {
        $this->actingAs($this->admin());
        $u = $this->apprenant();
        $this->post(route('admin.utilisateurs.suspend', $u), ['suspension_type' => 'temporaire'])->assertSessionHasErrors('suspended_until');
        $this->post(route('admin.utilisateurs.suspend', $u), ['suspension_type' => 'temporaire', 'suspended_until' => now()->subDay()->toDateString()])->assertSessionHasErrors('suspended_until');
        $this->post(route('admin.utilisateurs.suspend', $u), ['suspension_type' => 'temporaire', 'suspended_until' => now()->addDays(3)->toDateString(), 'suspension_reason' => 'abus'])->assertSessionHas('success');
        $u->refresh();
        $this->assertTrue($u->isSuspended());
        $this->assertSame('abus', $u->suspension_reason);

        $this->post(route('admin.utilisateurs.suspend', $u), ['suspension_type' => 'definitive'])->assertSessionHas('success');
        $this->assertNull($u->fresh()->suspended_until);
        $this->assertTrue($u->fresh()->isSuspended());

        $this->post(route('admin.utilisateurs.unsuspend', $u))->assertSessionHas('success');
        $this->assertFalse($u->fresh()->isSuspended());
    }

    public function test_suspended_user_is_blocked_immediately(): void
    {
        $admin = $this->admin();
        $u = $this->apprenant();
        $this->actingAs($admin)->post(route('admin.utilisateurs.suspend', $u), ['suspension_type' => 'definitive']);
        $this->post('/logout');
        $this->post('/login', ['email' => $u->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_admin_cannot_suspend_or_deactivate_self(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post(route('admin.utilisateurs.suspend', $admin), ['suspension_type' => 'definitive'])->assertSessionHas('error');
        $this->delete(route('admin.utilisateurs.destroy', $admin))->assertSessionHas('error');
        $this->assertFalse($admin->fresh()->isSuspended());
        $this->assertTrue($admin->fresh()->is_active);
    }

    public function test_admin_soft_deletes_user_who_then_cannot_login(): void
    {
        $u = $this->apprenant();
        $this->actingAs($this->admin())->delete(route('admin.utilisateurs.destroy', $u))->assertRedirect(route('admin.utilisateurs.index'));
        $u->refresh();
        $this->assertFalse($u->is_active);
        $this->assertNotNull(User::find($u->id)); // pas de suppression physique
        $this->post('/logout');
        $this->post('/login', ['email' => $u->email, 'password' => 'password'])->assertSessionHasErrors('email');
    }

    public function test_gestionnaire_cannot_manage_accounts_or_escalate(): void
    {
        $g = $this->gestionnaire();
        $victim = $this->apprenant();
        $admin = $this->admin();
        $this->actingAs($g);

        $this->put(route('admin.utilisateurs.update', $victim), ['name' => 'N', 'email' => $victim->email, 'role' => 'admin'])->assertForbidden();
        $this->put(route('admin.utilisateurs.update', $g), ['name' => 'N', 'email' => $g->email, 'role' => 'admin'])->assertForbidden();
        $this->post(route('admin.utilisateurs.resetPassword', $admin), ['new_password' => 'Pirate-Pass-123', 'new_password_confirmation' => 'Pirate-Pass-123'])->assertForbidden();
        $this->post(route('admin.utilisateurs.suspend', $admin), ['suspension_type' => 'definitive'])->assertForbidden();
        $this->delete(route('admin.utilisateurs.destroy', $admin))->assertForbidden();
        $this->get(route('admin.utilisateurs.edit', $admin))->assertForbidden();

        $this->assertSame('apprenant', $victim->fresh()->role);
        $this->assertSame('gestionnaire', $g->fresh()->role);
        $this->assertTrue(Hash::check('password', $admin->fresh()->password));
    }

    public function test_user_edit_page_escapes_user_data(): void
    {
        $u = $this->apprenant(['name' => '<script>alert(1)</script>']);
        $this->actingAs($this->admin())->get(route('admin.utilisateurs.edit', $u))->assertOk()->assertDontSee('<script>alert(1)</script>', false);
        $this->get(route('admin.utilisateurs.index'))->assertOk()->assertDontSee('<script>alert(1)</script>', false);
    }

    // ───────── Paiements ─────────

    public function test_payments_index_and_filter(): void
    {
        $this->actingAs($this->gestionnaire());
        $f = $this->formation(['titre' => 'Formation Pay']);
        $a = $this->apprenant(['name' => 'PayeurAttente']);
        $b = $this->apprenant(['name' => 'PayeurValide']);
        $this->paiement($a, $f);
        $this->paiement($b, $f, ['statut' => 'valide']);
        $this->get(route('admin.paiements.index'))->assertOk()->assertSee('PayeurAttente')->assertSee('PayeurValide');
        $this->get(route('admin.paiements.index', ['statut' => 'valide']))->assertOk()->assertSee('PayeurValide')->assertDontSee('PayeurAttente');
    }

    public function test_capture_is_streamed_from_private_disk_to_staff_only(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('paiements/preuve.png', $this->pngBytes());
        $p = $this->paiement($this->apprenant(), $this->formation(), ['capture_paiement' => 'paiements/preuve.png']);

        $this->actingAs($this->admin())->get(route('admin.paiements.capture', $p))->assertOk();
        $this->actingAs($this->gestionnaire())->get(route('admin.paiements.capture', $p))->assertOk();
        $this->actingAs($p->user)->get(route('admin.paiements.capture', $p))->assertForbidden();
        $this->actingAs($this->apprenant())->get(route('admin.paiements.capture', $p))->assertForbidden();
        auth()->logout();
        $this->get(route('admin.paiements.capture', $p))->assertRedirect(route('login'));
    }

    public function test_capture_missing_file_is_404_and_file_is_not_publicly_reachable(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        $p = $this->paiement($this->apprenant(), $this->formation(), ['capture_paiement' => 'paiements/nope.png']);
        $this->actingAs($this->admin())->get(route('admin.paiements.capture', $p))->assertNotFound();
        $this->assertContains($this->get('/storage/paiements/nope.png')->status(), [403, 404]);
        $this->assertContains($this->get('/storage/../storage/app/private/paiements/nope.png')->status(), [403, 404]);
    }

    public function test_validate_activates_inscription_debits_jetons_and_notifies(): void
    {
        Notification::fake();
        $learner = $this->apprenant(['jetons' => 15000]);
        $f = $this->formation();
        $p = $this->paiement($learner, $f, ['jetons_utilises' => 10000]);
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.paiements.valider', $p))->assertSessionHas('success');
        $p->refresh();
        $this->assertSame('valide', $p->statut);
        $this->assertSame($admin->id, $p->valide_par);
        $this->assertNotNull($p->valide_at);
        $this->assertSame('active', Inscription::where('user_id', $learner->id)->where('formation_id', $f->id)->first()->statut);
        $this->assertSame(5000, $learner->fresh()->jetons);
        Notification::assertSentTo($learner, PaiementValide::class);
    }

    public function test_cannot_validate_twice_and_jetons_debited_once(): void
    {
        Notification::fake();
        $learner = $this->apprenant(['jetons' => 15000]);
        $p = $this->paiement($learner, $this->formation(), ['jetons_utilises' => 10000]);
        $this->actingAs($this->admin());
        $this->post(route('admin.paiements.valider', $p))->assertSessionHas('success');
        $this->post(route('admin.paiements.valider', $p))->assertSessionHas('error');
        $this->assertSame(5000, $learner->fresh()->jetons);
        Notification::assertSentToTimes($learner, PaiementValide::class, 1);
    }

    public function test_validation_never_makes_jetons_negative(): void
    {
        Notification::fake();
        $learner = $this->apprenant(['jetons' => 100]);
        $p = $this->paiement($learner, $this->formation(), ['jetons_utilises' => 10000]);
        $this->actingAs($this->admin())->post(route('admin.paiements.valider', $p));
        $this->assertSame(0, $learner->fresh()->jetons);
    }

    public function test_reject_requires_note_and_notifies(): void
    {
        Notification::fake();
        $learner = $this->apprenant();
        $p = $this->paiement($learner, $this->formation());
        $this->actingAs($this->admin());
        $this->post(route('admin.paiements.rejeter', $p), [])->assertSessionHasErrors('note_admin');
        $this->post(route('admin.paiements.rejeter', $p), ['note_admin' => str_repeat('a', 501)])->assertSessionHasErrors('note_admin');
        $this->post(route('admin.paiements.rejeter', $p), ['note_admin' => 'Capture illisible'])->assertSessionHas('success');
        $p->refresh();
        $this->assertSame('rejete', $p->statut);
        $this->assertSame('Capture illisible', $p->note_admin);
        Notification::assertSentTo($learner, PaiementRejete::class);
        $this->assertDatabaseMissing('inscriptions', ['user_id' => $learner->id, 'statut' => 'active']);
    }

    public function test_already_validated_payment_cannot_be_rejected(): void
    {
        // FINDING : PaiementController::rejeter n'interdit pas de rejeter un paiement deja valide
        // (statut repasse a "rejete" mais inscription active et jetons debites restent).
        $learner = $this->apprenant();
        $p = $this->paiement($learner, $this->formation());
        $this->actingAs($this->admin());
        $this->post(route('admin.paiements.valider', $p));
        $this->post(route('admin.paiements.rejeter', $p), ['note_admin' => 'oups']);
        $this->assertSame('valide', $p->fresh()->statut);
    }

    public function test_rejected_payment_can_be_validated_later_activates_access(): void
    {
        Notification::fake();
        $learner = $this->apprenant();
        $f = $this->formation();
        $p = $this->paiement($learner, $f, ['statut' => 'rejete']);
        $this->actingAs($this->admin())->post(route('admin.paiements.valider', $p));
        $this->assertSame('active', Inscription::where('user_id', $learner->id)->first()->statut);
    }

    public function test_payment_unknown_id_404(): void
    {
        $this->actingAs($this->admin())->post('/admin/paiements/99999/valider')->assertNotFound();
        $this->assertSame(0, Paiement::count());
    }
}
