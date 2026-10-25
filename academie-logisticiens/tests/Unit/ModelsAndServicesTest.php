<?php

namespace Tests\Unit;

use App\Models\CodePromo;
use App\Models\FlagCapture;
use App\Models\NotificationInterne;
use App\Models\Parametre;
use App\Services\GamificationService;
use Tests\TestCase;

class ModelsAndServicesTest extends TestCase
{
    // ───────── Parametre ─────────

    public function test_site_titre_and_slogan_defaults(): void
    {
        $this->assertSame('CABINET ACLOG', Parametre::siteTitre());
        $this->assertSame('La compétence, rien que la compétence.', Parametre::siteSlogan());
    }

    public function test_site_titre_trims_and_falls_back_when_blank(): void
    {
        Parametre::definir('site_titre', '   Mon Cabinet   ');
        $this->assertSame('Mon Cabinet', Parametre::siteTitre());
        Parametre::definir('site_titre', '   ');
        $this->assertSame('CABINET ACLOG', Parametre::siteTitre());
    }

    public function test_slogan_can_be_emptied(): void
    {
        Parametre::definir('site_slogan', '');
        $this->assertSame('', Parametre::siteSlogan());
    }

    public function test_identifiants_legaux_defaults_override_and_line(): void
    {
        $d = Parametre::identifiantsLegaux();
        $this->assertSame(['RCCM', 'SCIEN', 'SCIET', 'NIU'], array_keys($d));
        $this->assertSame('CG-BZV-01-2026-A10-01925', $d['RCCM']);
        $this->assertSame('2173996', $d['SCIEN']);
        $this->assertSame('2173996017', $d['SCIET']);
        $this->assertSame('P230000004669493', $d['NIU']);

        Parametre::definir('legal_rccm', 'RCCM-X');
        Parametre::definir('legal_niu', '');
        $this->assertSame('RCCM-X', Parametre::identifiantsLegaux()['RCCM']);
        $ligne = Parametre::identifiantsLegauxLigne();
        $this->assertStringContainsString('RCCM: RCCM-X', $ligne);
        $this->assertStringContainsString(' | ', $ligne);
        $this->assertStringNotContainsString('NIU', $ligne); // valeur vide filtree
    }

    public function test_definir_invalidates_cache(): void
    {
        $this->assertSame('v1', Parametre::definir('cle_test', 'v1') ?? Parametre::valeur('cle_test'));
        $this->assertSame('v1', Parametre::valeur('cle_test'));
        Parametre::definir('cle_test', 'v2');
        $this->assertSame('v2', Parametre::valeur('cle_test'));
        $this->assertSame(1, Parametre::where('cle', 'cle_test')->count());
    }

    public function test_valeur_returns_default_when_missing(): void
    {
        $this->assertSame('defaut', Parametre::valeur('inexistant', 'defaut'));
        $this->assertNull(Parametre::valeur('autre_inexistant'));
    }

    public function test_valeur_does_not_serve_stale_default_after_row_is_created_elsewhere(): void
    {
        // FINDING : Cache::remember met en cache la valeur PAR DEFAUT (1h). Un parametre cree sans passer par
        // Parametre::definir (seeder, tinker, migration) reste invisible jusqu'a expiration.
        $this->assertSame('d', Parametre::valeur('cle_seed', 'd'));
        Parametre::create(['cle' => 'cle_seed', 'valeur' => 'reel']);
        $this->assertSame('reel', Parametre::valeur('cle_seed', 'd'));
    }

    public function test_valeur_with_different_defaults_does_not_collide(): void
    {
        // FINDING (meme cause) : le 1er defaut appele gagne pour tous les appelants.
        $this->assertSame(70, Parametre::valeur('score_x', 70));
        $this->assertSame(50, Parametre::valeur('score_x', 50));
    }

    // ───────── CodePromo ─────────

    public function test_promo_est_valide(): void
    {
        $this->assertTrue($this->promo()->estValide());
        $this->assertFalse($this->promo(['is_active' => false])->estValide());
        $this->assertFalse($this->promo(['date_debut' => now()->addHour()])->estValide());
        $this->assertTrue($this->promo(['date_debut' => now()->subHour()])->estValide());
        $this->assertFalse($this->promo(['date_fin' => now()->subMinute()])->estValide());
        $this->assertTrue($this->promo(['date_fin' => now()->addMinute()])->estValide());
        $this->assertFalse($this->promo(['nb_utilisations_max' => 3, 'nb_utilisations_actuel' => 3])->estValide());
        $this->assertFalse($this->promo(['nb_utilisations_max' => 3, 'nb_utilisations_actuel' => 4])->estValide());
        $this->assertTrue($this->promo(['nb_utilisations_max' => 3, 'nb_utilisations_actuel' => 2])->estValide());
        $this->assertTrue($this->promo(['nb_utilisations_max' => null, 'nb_utilisations_actuel' => 999])->estValide());
    }

    public function test_promo_se_applique_a(): void
    {
        $f = $this->formation(['categorie' => 'commerce']);
        $other = $this->formation(['categorie' => 'douane']);
        $this->assertTrue($this->promo(['perimetre' => 'all'])->seAppliqueA($f));
        $this->assertTrue($this->promo(['perimetre' => 'formation', 'formation_id' => $f->id])->seAppliqueA($f));
        $this->assertFalse($this->promo(['perimetre' => 'formation', 'formation_id' => $other->id])->seAppliqueA($f));
        $this->assertFalse($this->promo(['perimetre' => 'formation', 'formation_id' => null])->seAppliqueA($f));
        $this->assertTrue($this->promo(['perimetre' => 'categorie', 'categorie' => 'commerce'])->seAppliqueA($f));
        $this->assertFalse($this->promo(['perimetre' => 'categorie', 'categorie' => 'douane'])->seAppliqueA($f));
        $this->assertFalse($this->promo(['perimetre' => 'categorie', 'categorie' => null])->seAppliqueA($f));
    }

    // ───────── Formation ─────────

    public function test_prix_effectif(): void
    {
        $this->assertSame(1000, $this->formation(['prix' => 1000])->prix_effectif);
        $this->assertSame(800, $this->formation(['prix' => 1000, 'prix_promo' => 800, 'promo_active' => true])->prix_effectif);
        $this->assertSame(1000, $this->formation(['prix' => 1000, 'prix_promo' => 800, 'promo_active' => false])->prix_effectif);
        $this->assertSame(1000, $this->formation(['prix' => 1000, 'prix_promo' => null, 'promo_active' => true])->prix_effectif);
    }

    public function test_formation_route_key_is_slug(): void
    {
        $this->assertSame('slug', $this->formation()->getRouteKeyName());
    }

    // ───────── User ─────────

    public function test_user_roles_and_suspension_helpers(): void
    {
        $this->assertTrue($this->admin()->isAdmin());
        $this->assertTrue($this->admin()->isGestionnaire());
        $this->assertTrue($this->gestionnaire()->isGestionnaire());
        $this->assertFalse($this->gestionnaire()->isAdmin());
        $this->assertFalse($this->apprenant()->isGestionnaire());

        $u = $this->apprenant();
        $this->assertFalse($u->isSuspended());
        $u->suspended_at = now();
        $this->assertTrue($u->isSuspended());
        $u->suspended_until = now()->addDay();
        $this->assertTrue($u->isSuspended());
        $u->suspended_until = now()->subSecond();
        $this->assertFalse($u->isSuspended());
    }

    public function test_nom_complet(): void
    {
        $u = $this->apprenant(['prenom' => 'Jean', 'name' => 'Mabiala']);
        $this->assertSame('Jean Mabiala', $u->nom_complet);
    }

    // ───────── GamificationService ─────────

    public function test_points_below_threshold_are_kept(): void
    {
        $this->seedParametres();
        $u = $this->apprenant();
        app(GamificationService::class)->ajouterPoints($u, 40, 'raison');
        $u->refresh();
        $this->assertSame(40, $u->points);
        $this->assertSame(0, $u->jetons);
        $this->assertSame(1, NotificationInterne::where('user_id', $u->id)->where('type', 'points_gagnes')->count());
    }

    public function test_points_convert_to_jetons_with_remainder_and_multiples(): void
    {
        $this->seedParametres();
        $svc = app(GamificationService::class);
        $u = $this->apprenant(['points' => 90]);
        $svc->ajouterPoints($u, 10, 'x');
        $u->refresh();
        $this->assertSame(0, $u->points);
        $this->assertSame(5000, $u->jetons);

        $v = $this->apprenant(['points' => 0]);
        $svc->ajouterPoints($v, 250, 'x');
        $v->refresh();
        $this->assertSame(50, $v->points);
        $this->assertSame(10000, $v->jetons);
        $this->assertSame(1, NotificationInterne::where('user_id', $v->id)->where('type', 'jetons_credites')->count());
    }

    public function test_conversion_uses_parametres(): void
    {
        $this->seedParametres(['seuil_jetons' => 10, 'valeur_jetons_fcfa' => 1000]);
        $u = $this->apprenant();
        app(GamificationService::class)->ajouterPoints($u, 25, 'x');
        $u->refresh();
        $this->assertSame(5, $u->points);
        $this->assertSame(2000, $u->jetons);
    }

    public function test_jetons_accumulate_on_existing_balance(): void
    {
        $this->seedParametres();
        $u = $this->apprenant(['jetons' => 1234]);
        app(GamificationService::class)->ajouterPoints($u, 100, 'x');
        $this->assertSame(6234, $u->fresh()->jetons);
    }

    public function test_capturer_etape_is_idempotent(): void
    {
        $this->seedParametres();
        $u = $this->apprenant();
        $c = $this->challenge($this->parcours(), 1, 10, ['points' => 7]);
        $svc = app(GamificationService::class);
        $this->assertSame(7, $svc->capturerEtape($u, $c));
        $this->assertSame(0, $svc->capturerEtape($u, $c));
        $this->assertSame(0, $svc->capturerEtape($u, $c));
        $this->assertSame(7, $u->fresh()->points);
        $this->assertSame(1, FlagCapture::where('user_id', $u->id)->count());
    }

    public function test_capturer_etape_falls_back_to_default_points_param(): void
    {
        $this->seedParametres(['points_flag_capture' => 9]);
        $u = $this->apprenant();
        $c = $this->challenge($this->parcours(), 1, 10, ['points' => 0]);
        $this->assertSame(9, app(GamificationService::class)->capturerEtape($u, $c));
    }

    public function test_different_users_can_capture_same_step(): void
    {
        $this->seedParametres();
        $c = $this->challenge($this->parcours(), 1);
        $svc = app(GamificationService::class);
        $this->assertSame(5, $svc->capturerEtape($this->apprenant(), $c));
        $this->assertSame(5, $svc->capturerEtape($this->apprenant(), $c));
    }

    public function test_test_reussi_awards_configured_points(): void
    {
        $this->seedParametres(['points_test_reussi' => 3]);
        $u = $this->apprenant();
        app(GamificationService::class)->testReussi($u);
        $this->assertSame(3, $u->fresh()->points);
    }

    public function test_promo_model_helper_is_creatable(): void
    {
        $this->assertInstanceOf(CodePromo::class, $this->promo());
    }
}
