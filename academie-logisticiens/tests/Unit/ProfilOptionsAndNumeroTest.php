<?php

namespace Tests\Unit;

use App\Models\Certificat;
use App\Models\User;
use App\Support\ProfilOptions;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ProfilOptionsAndNumeroTest extends TestCase
{
    public function test_code_pays_known_unknown_and_null(): void
    {
        $this->assertSame('CG', ProfilOptions::codePays('Congo'));
        $this->assertSame('CD', ProfilOptions::codePays('Congo (RDC)'));
        $this->assertSame('SN', ProfilOptions::codePays('Sénégal'));
        $this->assertSame('CI', ProfilOptions::codePays("Côte d'Ivoire"));
        $this->assertSame('XX', ProfilOptions::codePays('Autre'));
        $this->assertSame('XX', ProfilOptions::codePays('Atlantide'));
        $this->assertSame('XX', ProfilOptions::codePays(null));
        $this->assertSame('XX', ProfilOptions::codePays(''));
    }

    public function test_every_country_has_a_two_letter_uppercase_code(): void
    {
        foreach (ProfilOptions::PAYS as $nom => $code) {
            $this->assertMatchesRegularExpression('/^[A-Z]{2}$/', $code, $nom);
        }
    }

    public function test_regles_cover_all_profile_fields(): void
    {
        $r = ProfilOptions::regles();
        foreach (['date_naissance', 'lieu_naissance', 'situation', 'niveau_etudes', 'profession', 'secteur_activite', 'etablissement', 'ville', 'pays', 'source_decouverte', 'accept_marketing'] as $k) {
            $this->assertArrayHasKey($k, $r);
        }
        $this->assertContains('required', $r['date_naissance']);
        $this->assertContains('required', $r['pays']);
        $this->assertContains('required_if:situation,etudiant', $r['niveau_etudes']);
        $this->assertContains('required_if:situation,professionnel', $r['profession']);
    }

    public function test_regles_validate_situations_as_expected(): void
    {
        $base = ['date_naissance' => '1990-01-01', 'lieu_naissance' => 'X', 'situation' => 'autre', 'pays' => 'Congo'];
        $ok = fn (array $d) => Validator::make(array_merge($base, $d), ProfilOptions::regles())->passes();
        $this->assertTrue($ok([]));
        $this->assertFalse($ok(['situation' => 'etudiant']));
        $this->assertTrue($ok(['situation' => 'etudiant', 'niveau_etudes' => 'master']));
        $this->assertFalse($ok(['situation' => 'professionnel']));
        $this->assertTrue($ok(['situation' => 'professionnel', 'profession' => 'Chauffeur']));
        $this->assertTrue($ok(['situation' => 'demandeur_emploi']));
        $this->assertFalse($ok(['situation' => 'martien']));
        $this->assertFalse($ok(['date_naissance' => now()->subYears(5)->toDateString()]));
        $this->assertTrue($ok(['date_naissance' => now()->subYears(11)->toDateString()]));
        $this->assertFalse($ok(['date_naissance' => '1919-12-31']));
        $this->assertFalse($ok(['pays' => 'Mordor']));
    }

    public function test_profil_incomplet(): void
    {
        $this->assertTrue(ProfilOptions::profilIncomplet(new User()));
        $u = new User(['lieu_naissance' => 'X', 'situation' => 'autre']);
        $u->date_naissance = '1990-01-01';
        $this->assertFalse(ProfilOptions::profilIncomplet($u));
    }

    public function test_messages_are_defined_in_french(): void
    {
        $m = ProfilOptions::messages();
        $this->assertArrayHasKey('date_naissance.required', $m);
        $this->assertArrayHasKey('niveau_etudes.required_if', $m);
    }

    // ───────── Certificat::formaterNumero ─────────

    public function test_formater_numero(): void
    {
        $this->assertSame('CFF-2026-ACLOG-CG-001CG', Certificat::formaterNumero(2026, 1, 'Congo'));
        $this->assertSame('CFF-2026-ACLOG-CG-012SN', Certificat::formaterNumero(2026, 12, 'Sénégal'));
        $this->assertSame('CFF-2027-ACLOG-CG-123CM', Certificat::formaterNumero(2027, 123, 'Cameroun'));
        $this->assertSame('CFF-2026-ACLOG-CG-1234XX', Certificat::formaterNumero(2026, 1234, null));
        $this->assertSame('CFF-2026-ACLOG-CG-007XX', Certificat::formaterNumero(2026, 7, 'Inconnu'));
    }

    public function test_numero_affiche_uses_stored_numero_or_computes_fallback(): void
    {
        $c = new Certificat(['numero' => 'CFF-2026-ACLOG-CG-005FR']);
        $this->assertSame('CFF-2026-ACLOG-CG-005FR', $c->numero_affiche);

        $u = $this->apprenant(['pays' => 'Gabon']);
        $f = $this->formation();
        $legacy = $this->certificat($u, $f, ['numero' => null, 'issued_at' => '2025-03-01 10:00:00']);
        $this->assertSame(sprintf('CFF-2025-ACLOG-CG-%03dGA', $legacy->id), $legacy->numero_affiche);
    }
}
