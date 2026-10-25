<?php

namespace Tests\Feature\Auth;

use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class PasswordFlowsTest extends TestCase
{
    public function test_reset_password_link_screen_and_request(): void
    {
        $this->get('/forgot-password')->assertOk();
        Notification::fake();
        $user = $this->apprenant();
        $this->post('/forgot-password', ['email' => $user->email])->assertSessionHas('status');
        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_reset_link_for_unknown_email_sends_nothing(): void
    {
        Notification::fake();
        $this->post('/forgot-password', ['email' => 'ghost@example.test']);
        Notification::assertNothingSent();
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();
        $user = $this->apprenant();
        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($n) use ($user) {
            $this->get('/reset-password/' . $n->token)->assertOk();
            $this->post('/reset-password', [
                'token' => $n->token, 'email' => $user->email,
                'password' => 'Nouveau-Passw0rd!z', 'password_confirmation' => 'Nouveau-Passw0rd!z',
            ])->assertSessionHasNoErrors()->assertRedirect(route('login'));

            return true;
        });
        $this->assertTrue(Hash::check('Nouveau-Passw0rd!z', $user->fresh()->password));
    }

    public function test_reset_with_invalid_token_fails(): void
    {
        $user = $this->apprenant();
        $this->post('/reset-password', [
            'token' => 'bad-token', 'email' => $user->email,
            'password' => 'Nouveau-Passw0rd!z', 'password_confirmation' => 'Nouveau-Passw0rd!z',
        ])->assertSessionHasErrors('email');
        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_password_update_requires_current_password_and_strength(): void
    {
        $user = $this->apprenant();
        $this->actingAs($user)->from('/profil')->put('/password', [
            'current_password' => 'password', 'password' => 'Nouveau-Passw0rd!z', 'password_confirmation' => 'Nouveau-Passw0rd!z',
        ])->assertSessionHasNoErrors()->assertRedirect('/profil');
        $this->assertTrue(Hash::check('Nouveau-Passw0rd!z', $user->fresh()->password));

        $this->from('/profil')->put('/password', [
            'current_password' => 'wrong', 'password' => 'Autre-Passw0rd!zz', 'password_confirmation' => 'Autre-Passw0rd!zz',
        ])->assertSessionHasErrorsIn('updatePassword', 'current_password');

        $this->from('/profil')->put('/password', [
            'current_password' => 'Nouveau-Passw0rd!z', 'password' => 'abc', 'password_confirmation' => 'abc',
        ])->assertSessionHasErrorsIn('updatePassword', 'password');
    }

    public function test_confirm_password_screen_and_flow(): void
    {
        $user = $this->apprenant();
        $this->actingAs($user)->get('/confirm-password')->assertOk();
        $this->post('/confirm-password', ['password' => 'password'])->assertRedirect()->assertSessionHasNoErrors();
        $this->post('/confirm-password', ['password' => 'wrong'])->assertSessionHasErrors();
    }

    // ───────── Verification email ─────────

    public function test_verify_notice_renders_for_unverified_and_redirects_verified(): void
    {
        $this->actingAs($this->apprenant(['email_verified_at' => null]))->get('/verify-email')->assertOk();
        $this->actingAs($this->apprenant())->get('/verify-email')->assertRedirect();
    }

    public function test_unverified_user_is_redirected_from_espace(): void
    {
        $this->actingAs($this->apprenant(['email_verified_at' => null]));
        foreach (['/espace/dashboard', '/espace/mes-formations', '/espace/challenges', '/espace/support', '/espace/certifications'] as $url) {
            $this->get($url)->assertRedirect(route('verification.notice'));
        }
    }

    public function test_email_can_be_verified_with_signed_link(): void
    {
        $user = $this->apprenant(['email_verified_at' => null]);
        Event::fake();
        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), ['id' => $user->id, 'hash' => sha1($user->email)]);
        $this->actingAs($user)->get($url)->assertRedirect(route('dashboard', absolute: false) . '?verified=1');
        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_email_not_verified_with_invalid_hash_unsigned_or_other_user(): void
    {
        $user = $this->apprenant(['email_verified_at' => null]);
        $bad = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), ['id' => $user->id, 'hash' => sha1('wrong')]);
        $this->actingAs($user)->get($bad)->assertForbidden();
        $unsigned = route('verification.verify', ['id' => $user->id, 'hash' => sha1($user->email)]);
        $this->get($unsigned)->assertForbidden();
        $this->assertFalse($user->fresh()->hasVerifiedEmail());

        $other = $this->apprenant(['email_verified_at' => null]);
        $cross = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), ['id' => $other->id, 'hash' => sha1($other->email)]);
        $this->actingAs($user)->get($cross)->assertForbidden();
        $this->assertFalse($other->fresh()->hasVerifiedEmail());
    }

    public function test_expired_verification_link_rejected(): void
    {
        $user = $this->apprenant(['email_verified_at' => null]);
        $url = URL::temporarySignedRoute('verification.verify', now()->subMinute(), ['id' => $user->id, 'hash' => sha1($user->email)]);
        $this->actingAs($user)->get($url)->assertForbidden();
    }

    public function test_verification_notification_can_be_resent(): void
    {
        Notification::fake();
        $user = $this->apprenant(['email_verified_at' => null]);
        $this->actingAs($user)->post('/email/verification-notification')->assertRedirect()->assertSessionHas('status');
        Notification::assertSentTo($user, \Illuminate\Auth\Notifications\VerifyEmail::class);
    }
}
