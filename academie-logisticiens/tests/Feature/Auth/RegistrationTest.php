<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\BienvenueApprenant;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    public function test_registration_screen_can_be_rendered(): void
    {
        $this->get('/register')->assertOk();
    }

    public function test_new_users_can_register_and_are_authenticated(): void
    {
        Notification::fake();
        $payload = $this->registrationPayload();

        $this->post('/register', $payload)->assertRedirect(route('apprenant.dashboard', absolute: false));

        $this->assertAuthenticated();
        $user = User::where('email', $payload['email'])->first();
        $this->assertNotNull($user);
        $this->assertSame('apprenant', $user->role);
        $this->assertTrue(Hash::check($payload['password'], $user->password));
        $this->assertNotSame($payload['password'], $user->password);
        $this->assertSame('Pointe-Noire', $user->lieu_naissance);
        Notification::assertSentTo($user, BienvenueApprenant::class);
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_fresh_registered_user_must_verify_email_before_espace(): void
    {
        Notification::fake();
        $this->post('/register', $this->registrationPayload());
        $this->get('/espace/dashboard')->assertRedirect(route('verification.notice', absolute: false));
    }

    public function test_registration_cannot_escalate_role_or_set_protected_fields(): void
    {
        Notification::fake();
        $payload = $this->registrationPayload([
            'role' => 'admin', 'points' => 9999, 'jetons' => 99999, 'is_active' => 0,
            'email_verified_at' => now()->toDateTimeString(), 'google_id' => 'x',
            'suspended_at' => now()->toDateTimeString(),
        ]);
        $this->post('/register', $payload);

        $u = User::where('email', $payload['email'])->firstOrFail();
        $this->assertSame('apprenant', $u->role);
        $this->assertSame(0, $u->points);
        $this->assertSame(0, $u->jetons);
        $this->assertTrue($u->is_active);
        $this->assertNull($u->email_verified_at);
        $this->assertNull($u->google_id);
        $this->assertNull($u->suspended_at);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('invalidRegistrations')]
    public function test_registration_validation_rules(array $override, string $errorField): void
    {
        Notification::fake();
        $this->post('/register', $this->registrationPayload($override))->assertSessionHasErrors($errorField);
        $this->assertGuest();
    }

    public static function invalidRegistrations(): array
    {
        return [
            'name missing' => [['name' => ''], 'name'],
            'prenom missing' => [['prenom' => ''], 'prenom'],
            'email invalid' => [['email' => 'not-an-email'], 'email'],
            'password not confirmed' => [['password_confirmation' => 'different'], 'password'],
            'password weak' => [['password' => 'abc', 'password_confirmation' => 'abc'], 'password'],
            'date missing' => [['date_naissance' => ''], 'date_naissance'],
            'date too recent' => [['date_naissance' => '2024-01-01'], 'date_naissance'],
            'date too old' => [['date_naissance' => '1900-01-01'], 'date_naissance'],
            'lieu missing' => [['lieu_naissance' => ''], 'lieu_naissance'],
            'situation missing' => [['situation' => ''], 'situation'],
            'situation invalid' => [['situation' => 'roi'], 'situation'],
            'etudiant sans niveau' => [['situation' => 'etudiant'], 'niveau_etudes'],
            'niveau invalid' => [['situation' => 'etudiant', 'niveau_etudes' => 'prof'], 'niveau_etudes'],
            'professionnel sans profession' => [['situation' => 'professionnel'], 'profession'],
            'pays invalid' => [['pays' => 'Atlantide'], 'pays'],
            'pays missing' => [['pays' => ''], 'pays'],
            'secteur invalid' => [['secteur_activite' => 'pirate'], 'secteur_activite'],
            'source invalid' => [['source_decouverte' => 'reve'], 'source_decouverte'],
            'telephone trop long' => [['telephone' => str_repeat('1', 21)], 'telephone'],
        ];
    }

    public function test_duplicate_email_rejected_case_insensitively(): void
    {
        Notification::fake();
        $u = $this->apprenant(['email' => 'dup@example.test']);
        $this->post('/register', $this->registrationPayload(['email' => 'DUP@example.test']))->assertSessionHasErrors('email');
    }

    public function test_duplicate_email_gets_a_neutral_message_that_does_not_reveal_the_account(): void
    {
        Notification::fake();
        $this->apprenant(['email' => 'pris@example.test']);

        $this->post('/register', $this->registrationPayload(['email' => 'pris@example.test']))
            ->assertSessionHasErrors(['email' => 'Désolé, nous ne pouvons pas créer votre compte avec cette adresse e-mail. Veuillez en utiliser une autre.']);
        $this->assertSame(1, User::where('email', 'pris@example.test')->count());
        $this->assertGuest();
    }

    public function test_etudiant_with_niveau_and_professionnel_with_profession_are_accepted(): void
    {
        Notification::fake();
        $this->post('/register', $this->registrationPayload(['situation' => 'etudiant', 'niveau_etudes' => 'licence', 'email' => 'e1@example.test']))->assertSessionHasNoErrors();
        auth()->logout();
        $this->post('/register', $this->registrationPayload(['situation' => 'professionnel', 'profession' => 'Transitaire', 'email' => 'p1@example.test']))->assertSessionHasNoErrors();
        $this->assertSame(2, User::count());
    }

    public function test_authenticated_users_cannot_see_registration(): void
    {
        $this->actingAs($this->apprenant())->get('/register')->assertRedirect();
    }

    public function test_registration_xss_in_name_is_escaped_when_rendered(): void
    {
        Notification::fake();
        $payload = $this->registrationPayload(['name' => '<script>alert(1)</script>']);
        $this->post('/register', $payload);
        $user = User::where('email', $payload['email'])->first();
        $user->forceFill(['email_verified_at' => now()])->save();
        $this->actingAs($user)->get('/profil')->assertOk()->assertDontSee('<script>alert(1)</script>', false);
    }
}
