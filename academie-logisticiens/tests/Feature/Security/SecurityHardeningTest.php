<?php

namespace Tests\Feature\Security;

use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    // ───────── CSRF ─────────

    /** Le middleware anti-CSRF est desactive quand APP_ENV=testing : on simule un environnement reel. */
    private function enableCsrf(): void
    {
        $this->app->instance('env', 'local');
    }

    public function test_post_without_csrf_token_is_rejected_with_419(): void
    {
        $this->enableCsrf();
        $this->post('/contact', ['nom' => 'a', 'email' => 'a@b.test', 'objet' => 'o', 'message' => 'm'])->assertStatus(419);
        $this->post('/login', ['email' => 'a@b.test', 'password' => 'x'])->assertStatus(419);
        $this->post('/register', $this->registrationPayload())->assertStatus(419);
        $this->post('/tests-de-niveau/soumettre', [])->assertStatus(419);
    }

    public function test_authenticated_mutations_without_csrf_token_are_rejected(): void
    {
        $u = $this->apprenant();
        $this->actingAs($u);
        $this->enableCsrf();
        $this->post('/logout')->assertStatus(419);
        $this->patch('/profil', $this->profilPayload($u, ['name' => 'Hacked']))->assertStatus(419);
        $this->post('/espace/support/ticket', ['sujet' => 's', 'message' => 'm'])->assertStatus(419);
        $this->post('/notifications/tout-lire')->assertStatus(419);
        $this->assertNotSame('Hacked', $u->fresh()->name);

        $admin = $this->admin();
        $this->actingAs($admin);
        $this->post("/admin/utilisateurs/{$u->id}/suspend", ['suspension_type' => 'definitive'])->assertStatus(419);
        $this->delete("/admin/utilisateurs/{$u->id}")->assertStatus(419);
        $this->assertTrue($u->fresh()->is_active);
    }

    public function test_post_with_valid_csrf_token_passes(): void
    {
        $this->enableCsrf();
        $this->withSession(['_token' => 'tok123'])
            ->post('/contact', ['_token' => 'tok123', 'nom' => 'a', 'email' => 'a@b.test', 'objet' => 'o', 'message' => 'm'])
            ->assertStatus(302);
        $this->withSession(['_token' => 'tok123'])
            ->withHeader('X-CSRF-TOKEN', 'tok123')
            ->post('/contact', ['nom' => 'a', 'email' => 'a@b.test', 'objet' => 'o', 'message' => 'm'])
            ->assertStatus(302);
    }

    public function test_forged_csrf_token_is_rejected(): void
    {
        $this->enableCsrf();
        $this->withSession(['_token' => 'real'])->post('/contact', ['_token' => 'forged', 'nom' => 'a', 'email' => 'a@b.test', 'objet' => 'o', 'message' => 'm'])->assertStatus(419);
    }

    // ───────── En-tetes de securite ─────────

    /** @group security-headers */
    public function test_security_headers_on_public_pages(): void
    {
        foreach (['/', '/formations', '/login', '/contact', '/page-inconnue-404'] as $url) {
            $res = $this->get($url);
            $this->assertSame('nosniff', $res->headers->get('X-Content-Type-Options'), $url);
            $this->assertNotEmpty($res->headers->get('Referrer-Policy'), $url);
            $this->assertNotEmpty($res->headers->get('Permissions-Policy'), $url);
            $csp = (string) $res->headers->get('Content-Security-Policy');
            $this->assertNotSame('', $csp, "CSP absente sur {$url}");
            $xfo = (string) $res->headers->get('X-Frame-Options');
            $this->assertTrue($xfo !== '' || str_contains($csp, 'frame-ancestors'), "anti-clickjacking absent sur {$url}");
        }
    }

    /** @group security-headers */
    public function test_security_headers_on_json_and_authenticated_responses(): void
    {
        $res = $this->postJson('/api/chatbot', ['question' => 'bonjour']);
        $this->assertSame('nosniff', $res->headers->get('X-Content-Type-Options'));

        $res = $this->actingAs($this->apprenant())->get('/espace/dashboard');
        $this->assertSame('nosniff', $res->headers->get('X-Content-Type-Options'));
        $this->assertStringContainsString('no-store', (string) $res->headers->get('Cache-Control'));

        $res = $this->actingAs($this->admin())->get('/admin/dashboard');
        $this->assertNotEmpty($res->headers->get('Content-Security-Policy'));
    }

    /** @group security-headers */
    public function test_csp_blocks_framing_plugins_and_base_uri_hijack(): void
    {
        $csp = (string) $this->get('/')->headers->get('Content-Security-Policy');
        $this->assertStringContainsString("frame-ancestors 'self'", $csp);
        $this->assertStringContainsString("object-src 'none'", $csp);
        $this->assertStringContainsString("base-uri 'self'", $csp);
        $this->assertStringContainsString("form-action 'self'", $csp);
        $this->assertStringContainsString("default-src 'self'", $csp);
    }

    /** @group security-headers */
    public function test_csp_script_src_does_not_allow_arbitrary_remote_origins(): void
    {
        $csp = (string) $this->get('/')->headers->get('Content-Security-Policy');
        preg_match("/script-src([^;]*)/", $csp, $m);
        $this->assertDoesNotMatchRegularExpression('#(https?:)?//\*|\s\*\s|\shttps:\s#', $m[1] ?? '');
    }

    /** @group security-headers */
    public function test_no_x_powered_by_header_leaks(): void
    {
        foreach (['/', '/login', '/admin/dashboard', '/nope'] as $url) {
            $this->assertFalse($this->get($url)->headers->has('X-Powered-By'), $url);
        }
        $this->assertFalse($this->postJson('/api/chatbot', ['question' => 'x'])->headers->has('X-Powered-By'));
    }

    /** @group security-headers */
    public function test_hsts_sent_over_https(): void
    {
        $res = $this->get('https://localhost/');
        $this->assertNotEmpty($res->headers->get('Strict-Transport-Security'));
    }

    // ───────── Cookies de session ─────────

    public function test_session_cookie_config_is_hardened(): void
    {
        $this->assertTrue(config('session.http_only'));
        $this->assertContains(strtolower((string) config('session.same_site')), ['lax', 'strict']);
    }

    public function test_session_cookie_is_httponly_and_samesite_on_the_wire(): void
    {
        $res = $this->get('/login');
        $cookie = collect($res->headers->getCookies())->first(fn ($c) => str_contains($c->getName(), 'session'));
        $this->assertNotNull($cookie);
        $this->assertTrue($cookie->isHttpOnly());
        $this->assertContains(strtolower((string) $cookie->getSameSite()), ['lax', 'strict']);
    }

    public function test_session_id_is_regenerated_on_login(): void
    {
        $user = $this->apprenant();
        $this->get('/login');
        $before = session()->getId();
        $this->post('/login', ['email' => $user->email, 'password' => 'password']);
        $this->assertNotSame($before, session()->getId());
    }

    public function test_logout_invalidates_session_and_csrf_token(): void
    {
        $this->actingAs($this->apprenant());
        $token = csrf_token();
        $this->post('/logout');
        $this->assertNotSame($token, csrf_token());
        $this->get('/espace/dashboard')->assertRedirect(route('login'));
    }

    // ───────── Fichiers sensibles ─────────

    public function test_sensitive_paths_are_not_served(): void
    {
        foreach (['/.env', '/.env.example', '/composer.json', '/composer.lock', '/artisan', '/storage/logs/laravel.log', '/.git/config',
                  '/vendor/autoload.php', '/phpunit.xml', '/database/database.sqlite', '/config/app.php', '/storage/app/private/x', '/../.env', '/%2e%2e/.env'] as $path) {
            $status = $this->get($path)->status();
            $this->assertContains($status, [403, 404], "{$path} -> {$status}");
        }
    }

    public function test_public_directory_has_no_stray_scripts_or_secrets(): void
    {
        $this->assertSame([realpath(public_path('index.php'))], array_map('realpath', glob(public_path('*.php'))));
        foreach (['.env', 'composer.json', 'phpinfo.php', 'info.php', 'test.php', 'adminer.php', '.git'] as $f) {
            $this->assertFileDoesNotExist(public_path($f));
        }
    }

    public function test_htaccess_denies_dotfiles_logs_and_manifests(): void
    {
        $ht = file_get_contents(public_path('.htaccess'));
        $this->assertStringContainsString('Require all denied', $ht);
        $this->assertStringContainsString('env', $ht);
        $this->assertStringContainsString('composer', $ht);
        $this->assertStringContainsString('-Indexes', $ht);
    }

    // ───────── Divers ─────────

    public function test_sqli_payloads_in_public_filters_do_not_error_or_leak(): void
    {
        $hidden = $this->unpublishedFormation(['titre' => 'Cachee Totalement']);
        $this->formation(['titre' => 'Visible Totalement']);
        $payloads = ["' OR '1'='1", "1; DROP TABLE formations;--", "' UNION SELECT * FROM users--", "%' OR 1=1 --", '" OR ""="', "\\", "'", str_repeat('A', 5000)];
        foreach ($payloads as $p) {
            foreach (['categorie', 'niveau', 'page'] as $param) {
                $res = $this->get('/formations?' . http_build_query([$param => $p]));
                $this->assertLessThan(500, $res->status(), "{$param}={$p}");
                $res->assertDontSee('Cachee Totalement');
            }
            $this->assertLessThan(500, $this->get('/annuaire-certifies?' . http_build_query(['q' => $p, 'code' => $p]))->status());
            $this->assertLessThan(500, $this->get('/formations/' . rawurlencode($p))->status());
            $this->assertLessThan(500, $this->get('/certificat/verifier/' . rawurlencode($p))->status());
            $this->resetThrottle();
        }
        $this->assertDatabaseCount('formations', 2);
        $this->assertNotNull($hidden->fresh());
    }

    public function test_sqli_in_admin_filters(): void
    {
        $this->actingAs($this->admin());
        foreach (["' OR 1=1 --", "1' UNION SELECT password FROM users --"] as $p) {
            $this->assertLessThan(500, $this->get('/admin/paiements?' . http_build_query(['statut' => $p]))->status());
            $this->assertLessThan(500, $this->get('/admin/tickets?' . http_build_query(['statut' => $p]))->status());
            $this->assertLessThan(500, $this->get('/admin/utilisateurs?' . http_build_query(['role' => $p, 'q' => $p]))->status());
        }
    }

    public function test_formation_title_and_names_are_escaped_on_public_pages(): void
    {
        $xss = '<script>alert("x")</script>';
        $f = $this->formation(['titre' => $xss, 'description' => $xss, 'categorie' => 'logistique']);
        $m = $this->module($f, ['titre' => $xss]);
        $this->lecon($m, ['titre' => $xss]);
        $u = $this->apprenant(['name' => $xss, 'prenom' => $xss]);
        $this->certificat($u, $f, ['is_public' => true]);

        foreach (['/', '/formations', "/formations/{$f->slug}", '/annuaire-certifies'] as $url) {
            $this->get($url)->assertDontSee($xss, false);
        }
        $c = $u->certificats()->first();
        $this->get('/certificat/verifier/' . $c->code_verification)->assertOk()->assertDontSee($xss, false);
    }

    public function test_lesson_text_containing_script_is_not_rendered_raw(): void
    {
        // FINDING (stored XSS) : lecon.blade.php:74 rend contenu_texte avec {!! !!} des qu'il contient "</".
        // Un gestionnaire (non-admin) peut ainsi executer du JS dans la session d'un admin qui ouvre la lecon.
        $fx = $this->formationComplete();
        $lecon = $fx['lecons'][0];
        $lecon->update(['contenu_texte' => '<p>ok</p><script>alert("stored")</script><img src=x onerror=alert(1)>']);
        $u = $this->apprenant();
        $insc = $this->inscrire($u, $fx['formation']);
        $res = $this->actingAs($u)->get(route('apprenant.cours.lecon', [$insc, $lecon]))->assertOk();
        $this->assertStringNotContainsString('<script>alert("stored")</script>', $res->getContent());
        $this->assertStringNotContainsString('onerror=alert(1)', $res->getContent());
    }

    public function test_formation_programme_with_script_is_not_rendered_raw(): void
    {
        // FINDING (stored XSS) : public/formations/show.blade.php:5 -> {!! programme !!} si contient "</".
        $f = $this->formation(['programme' => '<h3>Titre</h3><script>alert("prog")</script>']);
        $res = $this->get("/formations/{$f->slug}")->assertOk();
        $this->assertStringNotContainsString('<script>alert("prog")</script>', $res->getContent());
    }

    public function test_admin_pages_escape_user_controlled_content(): void
    {
        $xss = '<script>alert("a")</script>';
        $u = $this->apprenant(['name' => $xss]);
        $f = $this->formation(['titre' => $xss]);
        $this->paiement($u, $f);
        $this->ticket($u, ['sujet' => $xss]);
        $this->actingAs($this->admin());
        foreach (['/admin/dashboard', '/admin/paiements', '/admin/tickets', '/admin/utilisateurs', '/admin/formations'] as $url) {
            $this->get($url)->assertOk()->assertDontSee($xss, false);
        }
    }

    public function test_get_on_state_changing_routes_is_not_allowed(): void
    {
        $this->actingAs($this->apprenant());
        $this->get('/logout')->assertStatus(405);
        $this->get('/notifications/tout-lire')->assertStatus(405);
    }

    public function test_contact_form_rejects_header_injection(): void
    {
        $this->post('/contact', ['nom' => 'x', 'email' => "a@b.test\r\nBcc: victim@evil.test", 'objet' => 'o', 'message' => 'm'])->assertSessionHasErrors('email');
    }

    public function test_register_throttle_exists(): void
    {
        Notification::fake();
        $last = 0;
        for ($i = 0; $i < 15; $i++) {
            $last = $this->post('/register', $this->registrationPayload(['name' => '']))->status();
            if ($last === 429) {
                break;
            }
        }
        $this->assertSame(429, $last);
    }

    public function test_forgot_password_throttle_exists(): void
    {
        $last = 0;
        for ($i = 0; $i < 12; $i++) {
            $last = $this->post('/forgot-password', ['email' => 'x@example.test'])->status();
            if ($last === 429) {
                break;
            }
        }
        $this->assertSame(429, $last);
    }

    public function test_payment_submission_is_throttled(): void
    {
        $f = $this->formation();
        $u = $this->apprenant();
        $last = 0;
        for ($i = 0; $i < 10; $i++) {
            $last = $this->actingAs($u)->post(route('apprenant.paiement.confirmer', $f), [])->status();
            if ($last === 429) {
                break;
            }
        }
        $this->assertSame(429, $last);
    }

    public function test_forgot_password_does_not_reveal_account_existence(): void
    {
        // FINDING (faible) : message different pour un email inconnu (enumeration de comptes).
        Notification::fake();
        $u = $this->apprenant();
        $known = $this->post('/forgot-password', ['email' => $u->email]);
        $this->resetThrottle();
        $unknown = $this->post('/forgot-password', ['email' => 'inconnu@example.test']);
        $this->assertSame(
            $known->getSession()->has('errors'),
            $unknown->getSession()->has('errors'),
            'Reponse differente selon que le compte existe ou non'
        );
    }

    public function test_login_error_message_is_identical_for_unknown_user_and_wrong_password(): void
    {
        $u = $this->apprenant();
        $message = function ($res) {
            $errors = $res->getSession()->get('errors');
            $errors = is_array($errors) ? ($errors['default']['messages'] ?? []) : ($errors?->getBag('default')->toArray() ?? []);

            return (string) (($errors['email'] ?? [''])[0] ?? '');
        };
        $a = $message($this->post('/login', ['email' => $u->email, 'password' => 'bad']));
        $this->resetThrottle();
        $b = $message($this->post('/login', ['email' => 'nobody@example.test', 'password' => 'bad']));
        $this->assertNotSame('', $a);
        $this->assertSame($a, $b);
    }

    public function test_deactivated_user_cannot_reset_password_and_login(): void
    {
        Notification::fake();
        $u = $this->apprenant(['is_active' => false]);
        $this->post('/forgot-password', ['email' => $u->email]);
        $this->post('/login', ['email' => $u->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
