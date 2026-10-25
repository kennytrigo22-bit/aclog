<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

    public function test_email_verification_status_unchanged_when_email_same_and_reset_when_changed(): void
    {
        $user = $this->apprenant();
        $this->actingAs($user)->patch('/profil', $this->profilPayload($user))->assertSessionHasNoErrors();
        $this->assertNotNull($user->fresh()->email_verified_at);

        $this->patch('/profil', $this->profilPayload($user, ['email' => 'autre@example.test']))->assertSessionHasNoErrors();
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_email_must_be_unique_on_update(): void
    {
        $other = $this->apprenant();
        $user = $this->apprenant();
        $this->actingAs($user)->patch('/profil', $this->profilPayload($user, ['email' => $other->email]))->assertSessionHasErrors('email');
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
