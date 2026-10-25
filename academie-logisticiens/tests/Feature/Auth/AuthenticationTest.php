<?php

namespace Tests\Feature\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    public function test_login_screen_can_be_rendered(): void
    {
        $this->get('/login')->assertOk();
    }

    public function test_apprenant_logs_in_and_lands_on_apprenant_dashboard(): void
    {
        $user = $this->apprenant();
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('apprenant.dashboard', absolute: false));
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_and_gestionnaire_land_on_admin_dashboard(): void
    {
        foreach ([$this->admin(), $this->gestionnaire()] as $u) {
            $this->post('/login', ['email' => $u->email, 'password' => 'password'])
                ->assertRedirect(route('admin.dashboard', absolute: false));
            $this->post('/logout');
        }
    }

    public function test_wrong_password_and_unknown_email_fail(): void
    {
        $user = $this->apprenant();
        $this->post('/login', ['email' => $user->email, 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->post('/login', ['email' => 'nobody@example.test', 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_validation(): void
    {
        $this->post('/login', [])->assertSessionHasErrors(['email', 'password']);
        $this->post('/login', ['email' => 'bad', 'password' => 'x'])->assertSessionHasErrors('email');
    }

    public function test_login_is_throttled_after_five_failures(): void
    {
        Event::fake([Lockout::class]);
        $user = $this->apprenant();
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => $user->email, 'password' => 'wrong']);
        }
        // Meme avec le bon mot de passe : bloque.
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
        Event::assertDispatched(Lockout::class);
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = $this->apprenant(['is_active' => false]);
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_suspended_user_cannot_login_but_expired_suspension_can(): void
    {
        $user = $this->apprenant(['suspended_at' => now()->subDay(), 'suspended_until' => null]);
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();

        $user->forceFill(['suspended_until' => now()->addDays(2)])->save();
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();

        $user->forceFill(['suspended_until' => now()->subHour()])->save();
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect();
        $this->assertAuthenticated();
    }

    public function test_login_ignores_open_redirect_parameters(): void
    {
        $user = $this->apprenant();
        $res = $this->post('/login?redirect=https://evil.test&intended=https://evil.test', [
            'email' => $user->email, 'password' => 'password', 'redirect' => 'https://evil.test', 'redirect_to' => 'https://evil.test',
        ]);
        $this->assertStringNotContainsString('evil.test', (string) $res->headers->get('Location'));
    }

    public function test_intended_url_is_followed_after_login(): void
    {
        $user = $this->apprenant();
        $this->get('/espace/mes-formations')->assertRedirect('/login');
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect(url('/espace/mes-formations'));
    }

    public function test_users_can_logout(): void
    {
        $this->actingAs($this->apprenant())->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_guest_is_redirected_to_login_on_protected_routes(): void
    {
        foreach (['/espace/dashboard', '/profil', '/notifications', '/dashboard', '/admin/dashboard'] as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }
    }

    public function test_generic_dashboard_redirects_by_role(): void
    {
        $this->actingAs($this->apprenant())->get('/dashboard')->assertRedirect(route('apprenant.dashboard'));
        $this->actingAs($this->gestionnaire())->get('/dashboard')->assertRedirect(route('admin.dashboard'));
        $this->actingAs($this->admin())->get('/dashboard')->assertRedirect(route('admin.dashboard'));
    }

    public function test_deactivated_session_is_logged_out_by_middleware(): void
    {
        $user = $this->apprenant();
        $this->actingAs($user)->get('/espace/dashboard')->assertOk();
        $user->forceFill(['is_active' => false])->save();
        $this->get('/espace/dashboard')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_suspended_session_is_logged_out_by_middleware(): void
    {
        $user = $this->apprenant();
        $this->actingAs($user)->get('/espace/dashboard')->assertOk();
        $user->forceFill(['suspended_at' => now(), 'suspended_until' => null])->save();
        $this->get('/espace/dashboard')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_password_is_never_stored_in_clear(): void
    {
        $user = $this->apprenant();
        $this->assertNotSame('password', $user->password);
        $this->assertStringStartsWith('$2y$', $user->password);
    }
}
