<?php

namespace Tests\Feature\Admin;

use App\Models\Challenge;
use App\Models\Examen;
use App\Models\Formation;
use App\Models\Lecon;
use App\Models\Module;
use App\Models\Paiement;
use App\Models\Parcours;
use App\Models\Partenaire;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\TentativeExamen;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Routing\Route as LaravelRoute;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Chaque route /admin/* (toutes methodes) : invite -> login, apprenant -> 403, gestionnaire/admin -> acces.
 */
class AdminAccessTest extends TestCase
{
    /** @return array<string,mixed> valeurs de parametres d'URL reelles */
    private function params(): array
    {
        $f = $this->formationComplete();
        $learner = $this->apprenant();
        $parcours = $this->parcours();
        $challenge = $this->challenge($parcours, 1);
        $q = $f['quizzes'][0]->questions()->first();
        $examQ = $f['final']->questions()->first();
        $paiement = $this->paiement($learner, $f['formation']);
        $ticket = $this->ticket($learner);
        $promo = $this->promo();
        $partenaire = Partenaire::create(['nom' => 'P', 'logo' => 'partenaires/x.png', 'ordre' => 1, 'actif' => true]);
        $tentative = TentativeExamen::create([
            'user_id' => $learner->id, 'examen_id' => $f['final']->id, 'score' => 10, 'reponses_donnees' => [],
            'is_passed' => false, 'numero_tentative' => 1, 'is_active' => true, 'created_at' => now(),
        ]);

        $certificat = app(\App\Services\CertificatService::class)->genererPresentiel([
            'formation_id' => $f['formation']->id, 'titulaire_nom' => 'PRESENTIEL', 'titulaire_prenom' => 'Test', 'titulaire_pays' => 'Congo',
        ]);

        return [
            'formation' => $f['formation']->slug,
            'certificat' => $certificat->id,
            'temoignage' => \App\Models\Temoignage::create(['citation' => 'Citation', 'nom' => 'Nom Test'])->id,
            'module' => $f['modules'][0]->id,
            'lecon' => $f['lecons'][0]->id,
            'quiz' => $f['quizzes'][0]->id,
            'question' => $q->id,
            'examen' => $f['final']->id,
            'paiement' => $paiement->id,
            'user' => $learner->id,
            'ticket' => $ticket->id,
            'promo' => $promo->id,
            'parcours' => $parcours->slug,
            'challenge' => $challenge->id,
            'partenaire' => $partenaire->id,
            'tentative_examen' => $tentative->id,
            '_examQuestion' => $examQ->id,
        ];
    }

    /** @return \Illuminate\Support\Collection<int, array{0:string,1:string}> [method, uri avec params remplaces] */
    private function adminRoutes(array $p)
    {
        $out = collect();
        foreach (Route::getRoutes() as $route) {
            /** @var LaravelRoute $route */
            if (!str_starts_with($route->uri(), 'admin/') && $route->uri() !== 'admin') {
                continue;
            }
            $uri = $route->uri();
            foreach ($route->parameterNames() as $name) {
                $value = $p[$name] ?? null;
                // la route DELETE examens/questions/{question} cible une question d'examen
                if ($name === 'question' && str_contains($route->uri(), 'examens/questions')) {
                    $value = $p['_examQuestion'];
                }
                $this->assertNotNull($value, "Parametre de route non gere: {$name} dans {$route->uri()}");
                $uri = str_replace('{' . $name . '}', (string) $value, $uri);
            }
            foreach ($route->methods() as $m) {
                if ($m === 'HEAD') {
                    continue;
                }
                $out->push([$m, '/' . $uri]);
            }
        }

        return $out;
    }

    public function test_route_inventory_is_not_empty(): void
    {
        $this->assertGreaterThan(60, $this->adminRoutes($this->params())->count());
    }

    public function test_guests_are_redirected_to_login_on_every_admin_route(): void
    {
        $routes = $this->adminRoutes($this->params());
        foreach ($routes as [$method, $uri]) {
            $res = $this->call($method, $uri);
            $this->assertSame(302, $res->getStatusCode(), "{$method} {$uri} (invite) -> {$res->getStatusCode()}");
            $this->assertStringContainsString('/login', (string) $res->headers->get('Location'), "{$method} {$uri}");
            $this->resetThrottle();
        }
    }

    public function test_apprenants_get_403_on_every_admin_route(): void
    {
        $routes = $this->adminRoutes($this->params());
        $this->actingAs($this->apprenant());
        foreach ($routes as [$method, $uri]) {
            $res = $this->call($method, $uri);
            $this->assertSame(403, $res->getStatusCode(), "{$method} {$uri} (apprenant) -> {$res->getStatusCode()}");
            $this->resetThrottle();
        }
    }

    public function test_apprenant_with_email_verified_null_also_gets_403_or_redirect_not_200(): void
    {
        $this->actingAs($this->apprenant(['email_verified_at' => null]));
        $res = $this->get('/admin/dashboard');
        $this->assertContains($res->getStatusCode(), [302, 403]);
    }

    public function test_admin_and_gestionnaire_can_open_every_admin_get_page(): void
    {
        $p = $this->params();
        $routes = $this->adminRoutes($p)->filter(fn ($r) => $r[0] === 'GET');
        // routes GET qui renvoient une reponse particuliere (flux de fichier absent, redirect d'etat)
        $special = ['/admin/paiements/' . $p['paiement'] . '/capture' => [404], '/admin/qcm-import/apercu' => [302]];

        foreach ([$this->admin(), $this->gestionnaire()] as $user) {
            $this->actingAs($user);
            foreach ($routes as [$method, $uri]) {
                $res = $this->get($uri);
                $expected = $special[$uri] ?? [200];
                if ($user->role === 'gestionnaire' && $this->isAdminOnly($uri)) {
                    $expected = [403];
                }
                $this->assertContains($res->getStatusCode(), $expected, "{$user->role} GET {$uri} -> {$res->getStatusCode()}");
                $this->resetThrottle();
            }
        }
    }

    private function isAdminOnly(string $uri): bool
    {
        return str_starts_with($uri, '/admin/utilisateurs') || str_starts_with($uri, '/admin/parametres');
    }

    public function test_gestionnaire_is_blocked_from_account_and_settings_management_on_every_method(): void
    {
        $routes = $this->adminRoutes($this->params())->filter(fn ($r) => $this->isAdminOnly($r[1]));
        $this->assertGreaterThanOrEqual(9, $routes->count());
        $this->actingAs($this->gestionnaire());
        foreach ($routes as [$method, $uri]) {
            $this->assertSame(403, $this->call($method, $uri)->getStatusCode(), "gestionnaire {$method} {$uri}");
            $this->resetThrottle();
        }
    }

    public function test_admin_pages_are_not_cacheable_by_shared_caches(): void
    {
        $res = $this->actingAs($this->admin())->get('/admin/dashboard');
        $this->assertStringContainsString('no-store', (string) $res->headers->get('Cache-Control'));
    }
}
