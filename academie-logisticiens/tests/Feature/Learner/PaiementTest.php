<?php

namespace Tests\Feature\Learner;

use App\Models\Paiement;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaiementTest extends TestCase
{
    private User $u;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');
        $this->u = $this->apprenant();
    }

    private function capture(): UploadedFile
    {
        return $this->realUpload('preuve.png', $this->pngBytes());
    }

    private function payer($formation, array $data = [])
    {
        $this->resetThrottle();
        return $this->actingAs($this->u)->post(route('apprenant.paiement.confirmer', $formation), array_merge(['capture_paiement' => $this->capture()], $data));
    }

    public function test_initier_page_renders_for_published_formation(): void
    {
        $f = $this->formation(['titre' => 'Formation A Payer']);
        $this->actingAs($this->u)->get(route('apprenant.paiement.initier', $f))->assertOk()->assertSee('Formation A Payer');
    }

    public function test_initier_redirects_when_already_enrolled(): void
    {
        $f = $this->formation();
        $this->inscrire($this->u, $f);
        $this->actingAs($this->u)->get(route('apprenant.paiement.initier', $f))->assertRedirect(route('apprenant.cours.index'));
    }

    public function test_initier_unpublished_formation_is_404(): void
    {
        // FINDING : PaiementController::initier ne verifie pas is_published (confirmer le fait).
        $f = $this->unpublishedFormation();
        $this->actingAs($this->u)->get(route('apprenant.paiement.initier', $f))->assertNotFound();
    }

    public function test_confirmer_unpublished_formation_is_404(): void
    {
        $f = $this->unpublishedFormation();
        $this->payer($f)->assertNotFound();
        $this->assertSame(0, Paiement::count());
    }

    public function test_confirm_creates_pending_payment_stores_proof_on_private_disk(): void
    {
        $f = $this->formation(['prix' => 50000]);
        $this->payer($f)->assertOk();

        $p = Paiement::first();
        $this->assertSame('en_attente', $p->statut);
        $this->assertSame(50000, $p->montant);
        $this->assertSame($this->u->id, $p->user_id);
        Storage::disk('local')->assertExists($p->capture_paiement);
        $this->assertSame([], Storage::disk('public')->allFiles());
        $this->assertDatabaseHas('inscriptions', ['user_id' => $this->u->id, 'formation_id' => $f->id, 'statut' => 'en_attente']);
        $this->assertDatabaseHas('notifications_internes', ['user_id' => $this->u->id, 'type' => 'paiement_recu']);
    }

    public function test_proof_upload_accepts_jpg_png_pdf_only(): void
    {
        $f = $this->formation();
        foreach (['p.jpg' => $this->pngBytes(), 'p.jpeg' => $this->pngBytes()] as $name => $bytes) {
            // png sous extension jpg : accepte par mimes (extension devinee) ou refuse, mais jamais 500
            $this->assertLessThan(500, $this->payer($f, ['capture_paiement' => $this->realUpload($name, $bytes)])->status());
            Paiement::query()->delete();
        }
        $pdf = $this->realUpload('p.pdf', "%PDF-1.4\n1 0 obj<<>>endobj\ntrailer<<>>\n%%EOF", 'application/pdf');
        $this->payer($f, ['capture_paiement' => $pdf])->assertOk();
    }

    public function test_proof_upload_rejects_dangerous_or_wrong_files(): void
    {
        $f = $this->formation();
        foreach ($this->disguisedFiles('png') as $k => $file) {
            $this->payer($f, ['capture_paiement' => $file])->assertSessionHasErrors('capture_paiement');
        }
        $this->payer($f, ['capture_paiement' => UploadedFile::fake()->create('p.txt', 5, 'text/plain')])->assertSessionHasErrors('capture_paiement');
        $this->payer($f, ['capture_paiement' => UploadedFile::fake()->create('p.pdf', 6000, 'application/pdf')])->assertSessionHasErrors('capture_paiement');
        $this->payer($f, ['capture_paiement' => null])->assertSessionHasErrors('capture_paiement');
        $this->assertSame(0, Paiement::count());
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_duplicate_pending_payment_is_blocked(): void
    {
        $f = $this->formation();
        $this->payer($f)->assertOk();
        $this->payer($f)->assertRedirect(route('apprenant.dashboard'));
        $this->assertSame(1, Paiement::count());
    }

    public function test_already_enrolled_cannot_pay_again(): void
    {
        $f = $this->formation();
        $this->inscrire($this->u, $f);
        $this->payer($f)->assertRedirect(route('apprenant.cours.index'));
        $this->assertSame(0, Paiement::count());
    }

    public function test_rejected_payment_allows_a_new_attempt(): void
    {
        $f = $this->formation();
        $this->paiement($this->u, $f, ['statut' => 'rejete']);
        $this->payer($f)->assertOk();
        $this->assertSame(2, Paiement::count());
    }

    // ───────── Promo ─────────

    public function test_percentage_promo_applies_and_increments_usage(): void
    {
        $f = $this->formation(['prix' => 50000]);
        $promo = $this->promo(['code' => 'REMISE10', 'valeur' => 10]);
        $this->payer($f, ['code_promo' => 'REMISE10'])->assertOk();
        $p = Paiement::first();
        $this->assertSame(45000, $p->montant_promo);
        $this->assertSame($promo->id, $p->code_promo_id);
        $this->assertSame(1, $promo->fresh()->nb_utilisations_actuel);
    }

    public function test_fixed_promo_never_goes_below_zero(): void
    {
        $f = $this->formation(['prix' => 3000]);
        $this->promo(['code' => 'FIXE5K', 'type' => 'fixe', 'valeur' => 5000]);
        $this->payer($f, ['code_promo' => 'FIXE5K'])->assertOk();
        $this->assertSame(0, Paiement::first()->montant_promo);
    }

    public function test_promo_scope_and_validity_are_enforced(): void
    {
        $f = $this->formation(['categorie' => 'logistique']);
        $other = $this->formation(['categorie' => 'douane']);
        $cases = [
            'WRONGFORM' => ['perimetre' => 'formation', 'formation_id' => $other->id],
            'WRONGCAT' => ['perimetre' => 'categorie', 'categorie' => 'douane'],
            'INACTIVE' => ['is_active' => false],
            'EXPIRED' => ['date_fin' => now()->subDay()],
            'FUTURE' => ['date_debut' => now()->addDay()],
            'MAXED' => ['nb_utilisations_max' => 1, 'nb_utilisations_actuel' => 1],
        ];
        foreach ($cases as $code => $attrs) {
            $this->promo(array_merge(['code' => $code], $attrs));
            $this->payer($f, ['code_promo' => $code])->assertOk();
            $p = Paiement::latest('id')->first();
            $this->assertNull($p->code_promo_id, "{$code} ne doit pas s'appliquer");
            $this->assertNull($p->montant_promo, "{$code} ne doit pas s'appliquer");
            Paiement::query()->delete();
        }
        $this->assertSame(1, (int) \App\Models\CodePromo::sum('nb_utilisations_actuel')); // seul MAXED (pre-rempli)
    }

    public function test_scoped_promos_apply_when_matching(): void
    {
        $f = $this->formation(['categorie' => 'logistique', 'prix' => 10000]);
        $this->promo(['code' => 'GOODCAT', 'perimetre' => 'categorie', 'categorie' => 'logistique', 'valeur' => 50]);
        $this->payer($f, ['code_promo' => 'GOODCAT'])->assertOk();
        $this->assertSame(5000, Paiement::first()->montant_promo);
        Paiement::query()->delete();
        $this->promo(['code' => 'GOODFORM', 'perimetre' => 'formation', 'formation_id' => $f->id, 'valeur' => 20]);
        $this->payer($f, ['code_promo' => 'GOODFORM'])->assertOk();
        $this->assertSame(8000, Paiement::first()->montant_promo);
    }

    public function test_unknown_promo_is_ignored(): void
    {
        $f = $this->formation();
        $this->payer($f, ['code_promo' => 'NOPE'])->assertOk();
        $this->assertNull(Paiement::first()->code_promo_id);
    }

    public function test_promo_percentage_over_100_cannot_yield_negative_amount(): void
    {
        $f = $this->formation(['prix' => 10000]);
        $this->promo(['code' => 'CRAZY', 'valeur' => 150]);
        $this->payer($f, ['code_promo' => 'CRAZY'])->assertOk();
        $this->assertGreaterThanOrEqual(0, Paiement::first()->montant_promo ?? 0, 'montant_promo negatif avec un code >100%');
    }

    // ───────── Jetons ─────────

    public function test_jetons_are_recorded_but_not_debited_until_validation(): void
    {
        $this->u->forceFill(['jetons' => 20000])->save();
        $f = $this->formation(['prix' => 50000]);
        $this->payer($f, ['utiliser_jetons' => 1])->assertOk();
        $this->assertSame(20000, Paiement::first()->jetons_utilises);
        $this->assertSame(20000, $this->u->fresh()->jetons);
    }

    public function test_jetons_capped_by_price(): void
    {
        $this->u->forceFill(['jetons' => 99999])->save();
        $f = $this->formation(['prix' => 10000]);
        $this->payer($f, ['utiliser_jetons' => 1])->assertOk();
        $this->assertSame(10000, Paiement::first()->jetons_utilises);
    }

    public function test_jetons_not_used_when_user_has_none_or_option_off(): void
    {
        $f = $this->formation();
        $this->payer($f, ['utiliser_jetons' => 1])->assertOk();
        $this->assertSame(0, Paiement::first()->jetons_utilises);
        Paiement::query()->delete();
        $this->u->forceFill(['jetons' => 5000])->save();
        $this->payer($f)->assertOk();
        $this->assertSame(0, Paiement::first()->jetons_utilises);
    }

    public function test_same_jetons_cannot_be_reserved_for_two_formations_beyond_balance(): void
    {
        // FINDING : jetons non reserves a la demande ; deux paiements en attente peuvent consommer le meme solde.
        $this->u->forceFill(['jetons' => 10000])->save();
        $f1 = $this->formation(['prix' => 10000]);
        $f2 = $this->formation(['prix' => 10000]);
        $this->payer($f1, ['utiliser_jetons' => 1]);
        $this->payer($f2, ['utiliser_jetons' => 1]);
        $this->assertLessThanOrEqual(10000, (int) Paiement::sum('jetons_utilises'), 'Solde de jetons depense deux fois.');
    }

    public function test_guest_and_gestionnaire_cannot_pay(): void
    {
        $f = $this->formation();
        auth()->logout();
        $this->post(route('apprenant.paiement.confirmer', $f), ['capture_paiement' => $this->capture()])->assertRedirect(route('login'));
        $this->actingAs($this->gestionnaire())->post(route('apprenant.paiement.confirmer', $f), ['capture_paiement' => $this->capture()])->assertForbidden();
    }
}
