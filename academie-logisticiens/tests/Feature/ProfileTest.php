<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ConfirmerNouvelEmail;
use App\Notifications\EmailModifie;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    public function test_profile_page_is_displayed(): void
    {
        $this->actingAs($this->apprenant())->get('/profil')->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = $this->apprenant();
        $this->actingAs($user)->patch('/profil', $this->profilPayload($user, [
            'name' => 'Nouveau Nom', 'prenom' => 'Nouveau Prenom', 'ville' => 'Dolisie', 'pays' => 'Gabon', 'telephone' => '0600',
        ]))->assertSessionHasNoErrors()->assertRedirect('/profil');

        $user->refresh();
        $this->assertSame('Nouveau Nom', $user->name);
        $this->assertSame('Gabon', $user->pays);
        $this->assertSame('Dolisie', $user->ville);
    }

    public function test_email_change_waits_for_confirmation_link_sent_to_new_address(): void
    {
        Notification::fake();
        $user = $this->apprenant();
        $ancien = $user->email;

        $this->actingAs($user)->patch('/profil', $this->profilPayload($user))->assertSessionHasNoErrors();
        Notification::assertNothingSent();

        $this->patch('/profil', $this->profilPayload($user, ['email' => 'Nouvelle@Example.test']))
            ->assertSessionHasNoErrors()->assertSessionHas('status', 'email-change-sent');

        $user->refresh();
        $this->assertSame($ancien, $user->email, "l'ancienne adresse reste active");
        $this->assertNotNull($user->email_verified_at);
        $this->assertSame('nouvelle@example.test', $user->pending_email);
        $this->get('/profil')->assertSee('En attente de confirmation')->assertSee('nouvelle@example.test');

        $lien = null;
        Notification::assertSentOnDemand(ConfirmerNouvelEmail::class, function ($n, $channels, $notifiable) use (&$lien, $user) {
            $lien = $n->toMail($user)->actionUrl;

            return $notifiable->routes['mail'] === 'nouvelle@example.test';
        });

        // le lien fonctionne meme depuis un autre appareil, sans etre connecte
        auth()->logout();
        $this->get($lien)->assertRedirect(route('login'))->assertSessionHas('status');
        $user->refresh();
        $this->assertSame('nouvelle@example.test', $user->email);
        $this->assertNull($user->pending_email);
        $this->assertNotNull($user->email_verified_at);
        Notification::assertSentOnDemand(EmailModifie::class, fn ($n, $c, $notifiable) => $notifiable->routes['mail'] === $ancien);

        // lien deja utilise : plus valable
        $this->get($lien)->assertSessionHasErrors('email');
    }

    public function test_confirmation_link_must_be_signed_and_match_latest_request(): void
    {
        Notification::fake();
        $user = $this->apprenant();
        $this->actingAs($user)->patch('/profil', $this->profilPayload($user, ['email' => 'premiere@example.test']));
        $ancienLien = URL::temporarySignedRoute('profile.email.confirm', now()->addHour(), ['user' => $user->id, 'email' => 'premiere@example.test']);
        $this->patch('/profil', $this->profilPayload($user, ['email' => 'seconde@example.test']));

        $this->get(route('profile.email.confirm', ['user' => $user->id, 'email' => 'seconde@example.test']))->assertForbidden();
        $this->get(str_replace('premiere', 'pirate', $ancienLien))->assertForbidden();
        $this->get($ancienLien)->assertSessionHasErrors('email');
        $this->assertNotSame('premiere@example.test', $user->fresh()->email);

        $expire = URL::temporarySignedRoute('profile.email.confirm', now()->subMinute(), ['user' => $user->id, 'email' => 'seconde@example.test']);
        $this->get($expire)->assertForbidden();
    }

    public function test_taken_email_gets_a_neutral_message_and_nothing_is_sent(): void
    {
        Notification::fake();
        $other = $this->apprenant();
        $user = $this->apprenant();

        $this->actingAs($user)->patch('/profil', $this->profilPayload($user, ['email' => $other->email]))
            ->assertSessionHasErrors(['email' => User::MESSAGE_EMAIL_INDISPONIBLE]);
        $this->assertNull($user->fresh()->pending_email);
        Notification::assertNothingSent();
    }

    public function test_email_change_can_be_cancelled_and_is_rate_limited(): void
    {
        Notification::fake();
        $user = $this->apprenant();
        $this->actingAs($user);
        foreach (['a1', 'a2', 'a3'] as $n) {
            $this->patch('/profil', $this->profilPayload($user, ['email' => "{$n}@example.test"]))->assertSessionHasNoErrors();
        }
        $this->patch('/profil', $this->profilPayload($user, ['email' => 'a4@example.test']))->assertSessionHasErrors('email');
        $this->assertSame('a3@example.test', $user->fresh()->pending_email);

        $this->delete(route('profile.email.cancel'))->assertSessionHas('status', 'email-change-cancelled');
        $this->assertNull($user->fresh()->pending_email);
    }

    public function test_mass_assignment_of_protected_fields_is_ignored(): void
    {
        $user = $this->apprenant(['points' => 3, 'jetons' => 7]);
        $this->actingAs($user)->patch('/profil', $this->profilPayload($user, [
            'role' => 'admin', 'points' => 9999, 'jetons' => 99999, 'is_active' => 0,
            'email_verified_at' => null, 'suspended_at' => now()->toDateTimeString(), 'google_id' => 'g',
        ]))->assertSessionHasNoErrors();

        $user->refresh();
        $this->assertSame('apprenant', $user->role);
        $this->assertSame(3, $user->points);
        $this->assertSame(7, $user->jetons);
        $this->assertTrue($user->is_active);
        $this->assertNotNull($user->email_verified_at);
        $this->assertNull($user->suspended_at);
        $this->assertNull($user->google_id);
    }

    public function test_situation_rules_on_profile(): void
    {
        $user = $this->apprenant();
        $this->actingAs($user)->patch('/profil', $this->profilPayload($user, ['situation' => 'etudiant']))->assertSessionHasErrors('niveau_etudes');
        $this->patch('/profil', $this->profilPayload($user, ['situation' => 'professionnel']))->assertSessionHasErrors('profession');
        $this->patch('/profil', $this->profilPayload($user, ['situation' => 'etudiant', 'niveau_etudes' => 'master']))->assertSessionHasNoErrors();
        $this->patch('/profil', $this->profilPayload($user, ['situation' => 'professionnel', 'profession' => 'Douanier']))->assertSessionHasNoErrors();
        $this->patch('/profil', $this->profilPayload($user, ['situation' => 'x']))->assertSessionHasErrors('situation');
        $this->patch('/profil', $this->profilPayload($user, ['date_naissance' => '2025-01-01']))->assertSessionHasErrors('date_naissance');
        $this->patch('/profil', $this->profilPayload($user, ['pays' => 'Narnia']))->assertSessionHasErrors('pays');
    }

    public function test_valid_avatar_upload_and_removal(): void
    {
        Storage::fake('public');
        $user = $this->apprenant();
        $this->actingAs($user)->patch('/profil', $this->profilPayload($user, ['avatar' => UploadedFile::fake()->image('me.jpg', 100, 100)]))
            ->assertSessionHasNoErrors();
        $user->refresh();
        $this->assertNotNull($user->avatar);
        Storage::disk('public')->assertExists($user->avatar);
        $old = $user->avatar;

        $this->delete('/profil/avatar')->assertRedirect('/profil');
        $this->assertNull($user->fresh()->avatar);
        Storage::disk('public')->assertMissing($old);
    }

    public function test_avatar_rejects_non_images_and_disguised_files(): void
    {
        Storage::fake('public');
        $user = $this->apprenant();
        $this->actingAs($user);
        $bad = $this->disguisedFiles('jpg') + ['big' => UploadedFile::fake()->image('big.jpg')->size(3000)];
        foreach ($bad as $i => $file) {
            $this->patch('/profil', $this->profilPayload($user, ['avatar' => $file]))->assertSessionHasErrors('avatar');
        }
        $this->assertNull($user->fresh()->avatar);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = $this->apprenant();
        $this->actingAs($user)->delete('/profil', ['password' => 'password'])->assertSessionHasNoErrors()->assertRedirect('/');
        $this->assertGuest();
        $this->assertNull(User::find($user->id));
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = $this->apprenant();
        $this->actingAs($user)->from('/profil')->delete('/profil', ['password' => 'wrong'])->assertSessionHasErrorsIn('userDeletion', 'password');
        $this->assertNotNull($user->fresh());
        $this->delete('/profil', [])->assertSessionHasErrorsIn('userDeletion', 'password');
    }

    public function test_profile_is_available_to_admin_too(): void
    {
        $this->actingAs($this->admin())->get('/profil')->assertOk();
    }
}
