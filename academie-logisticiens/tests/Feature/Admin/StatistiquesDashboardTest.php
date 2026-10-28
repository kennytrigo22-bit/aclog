<?php

namespace Tests\Feature\Admin;

use App\Models\Presence;
use App\Models\Visite;
use App\Services\StatistiquesAdmin;
use Tests\TestCase;

class StatistiquesDashboardTest extends TestCase
{
    // ───────── Suivi des visites ─────────

    public function test_public_page_views_are_recorded_with_country_device_and_formation(): void
    {
        $f = $this->formation(['titre' => 'Formation Suivie']);

        $this->withHeaders(['CF-IPCountry' => 'CM', 'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0) Mobile', 'Referer' => 'https://www.facebook.com/page'])
            ->get("/formations/{$f->slug}")->assertOk()->assertCookie('aclog_v');

        $v = Visite::firstOrFail();
        $this->assertSame("/formations/{$f->slug}", $v->chemin);
        $this->assertSame($f->id, $v->formation_id);
        $this->assertSame('CM', $v->pays);
        $this->assertSame('mobile', $v->appareil);
        $this->assertSame('facebook.com', $v->referent);
        $this->assertSame(1, Presence::count());
    }

    public function test_same_browser_is_one_visitor_and_bots_staff_admin_pages_are_ignored(): void
    {
        $premiere = $this->get('/');
        $cookie = $premiere->getCookie('aclog_v')->getValue();
        $this->withCookie('aclog_v', $cookie)->get('/contact');
        $this->assertSame(2, Visite::count());
        $this->assertSame(1, Visite::distinct()->count('visiteur'));

        $this->withHeaders(['User-Agent' => 'Googlebot/2.1 (+http://www.google.com/bot.html)'])->get('/');
        $this->actingAs($this->admin())->get('/');
        $this->get('/admin/dashboard');
        $this->assertSame(2, Visite::count());
    }

    public function test_learner_country_comes_from_profile_without_cloudflare(): void
    {
        $this->actingAs($this->apprenant(['pays' => 'Gabon']))->get('/formations')->assertOk();

        $this->assertSame('GA', Visite::firstOrFail()->pays);
    }

    public function test_presence_heartbeat_keeps_visitor_online(): void
    {
        $this->postJson('/presence', ['chemin' => '/formations?x=1'])->assertNoContent();

        $p = Presence::firstOrFail();
        $this->assertSame('/formations', $p->chemin);
        $this->assertSame(1, Presence::enLigne()->count());

        $p->forceFill(['derniere_activite' => now()->subMinutes(10)])->save();
        $this->assertSame(0, Presence::enLigne()->count());
        $this->assertSame(0, Visite::count(), 'le signal de vie ne compte pas comme une page vue');
    }

    // ───────── Calcul des statistiques ─────────

    public function test_statistics_count_visitors_sales_revenue_and_rank_formations(): void
    {
        $vendue = $this->formation(['titre' => 'La Plus Vendue', 'prix' => 100000]);
        $autre = $this->formation(['titre' => 'Autre Formation', 'prix' => 50000]);
        $client = $this->apprenant(['pays' => 'Congo']);

        foreach ([['a', 'CG'], ['a', 'CG'], ['b', 'CM'], ['c', null]] as [$v, $pays]) {
            Visite::create(['visiteur' => str_repeat($v, 40), 'chemin' => "/formations/{$autre->slug}", 'formation_id' => $autre->id, 'pays' => $pays, 'appareil' => 'mobile']);
        }
        // hors periode : ne compte pas
        Visite::forceCreate(['visiteur' => str_repeat('z', 40), 'chemin' => '/', 'appareil' => 'ordinateur', 'created_at' => now()->subYears(2)]);

        $this->paiement($client, $vendue, ['statut' => 'valide', 'montant' => 100000, 'montant_promo' => 80000, 'jetons_utilises' => 5000, 'valide_at' => now()]);
        $this->paiement($client, $vendue, ['statut' => 'valide', 'montant' => 100000, 'valide_at' => now()]);
        $this->paiement($client, $autre, ['statut' => 'valide', 'montant' => 50000, 'valide_at' => now()]);
        $this->paiement($client, $autre, ['statut' => 'en_attente', 'montant' => 50000]);

        $s = app(StatistiquesAdmin::class)->calculer('mois');

        $this->assertSame(3, $s['kpis']['visiteurs']['valeur']);
        $this->assertSame(4, $s['kpis']['pages_vues']['valeur']);
        $this->assertSame(3, $s['kpis']['ventes']['valeur']);
        $this->assertSame(75000 + 100000 + 50000, $s['kpis']['chiffre_affaires']['valeur'], 'promo et jetons deduits');
        $this->assertSame(100.0, $s['kpis']['conversion']['valeur']);
        $this->assertSame('La Plus Vendue', $s['top']['formations_vendues'][0]['nom']);
        $this->assertSame(2, $s['top']['formations_vendues'][0]['valeur']);
        $this->assertSame('Autre Formation', $s['top']['formations_consultees'][0]['nom']);
        $this->assertCount(30, $s['libelles']);
        $this->assertSame(3.0, array_sum($s['series']['visiteurs']) > 0 ? (float) max($s['series']['visiteurs']) : 0.0);
        $this->assertEqualsCanonicalizing(['Congo', 'Cameroun', 'Inconnu'], array_column($s['top']['pays'], 'nom'));

        // filtre pays : visites du Congo seulement, ventes des apprenants congolais
        $cg = app(StatistiquesAdmin::class)->calculer('annee', 'CG');
        $this->assertSame(1, $cg['kpis']['visiteurs']['valeur']);
        $this->assertSame(3, $cg['kpis']['ventes']['valeur']);
        $this->assertCount(12, $cg['libelles']);
        $this->assertSame(0, app(StatistiquesAdmin::class)->calculer('jour', 'GA')['kpis']['ventes']['valeur']);
    }

    public function test_dashboard_and_json_endpoints(): void
    {
        $this->formation(['titre' => 'Formation Tableau']);
        $admin = $this->admin();

        $this->actingAs($admin)->get('/admin/dashboard?periode=semaine')->assertOk()
            ->assertSee('En ligne maintenant')->assertSee('Formations les plus vendues')->assertSee('stats-initiales', false);

        $this->getJson('/admin/dashboard/statistiques?periode=jour&pays=CG')->assertOk()
            ->assertJsonPath('periode', 'jour')->assertJsonPath('pays', 'CG')->assertJsonCount(24, 'libelles');
        $this->getJson('/admin/dashboard/statistiques?periode=nimporte&pays=<script>')->assertOk()
            ->assertJsonPath('periode', 'mois')->assertJsonPath('pays', null);
        $this->getJson('/admin/dashboard/en-ligne')->assertOk()->assertJsonStructure(['total', 'connectes', 'pages', 'heure']);

        $this->actingAs($this->gestionnaire())->getJson('/admin/dashboard/statistiques')->assertOk();
        $this->actingAs($this->apprenant())->getJson('/admin/dashboard/statistiques')->assertForbidden();
    }
}
