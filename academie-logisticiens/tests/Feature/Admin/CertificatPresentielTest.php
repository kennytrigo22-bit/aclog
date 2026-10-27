<?php

namespace Tests\Feature\Admin;

use App\Models\Certificat;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CertificatPresentielTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_admin_generates_presentiel_certificate_with_photo_and_it_is_public_and_verifiable(): void
    {
        $this->actingAs($this->admin());
        $f = $this->formation(['titre' => 'Douane Presentiel', 'duree_heures' => 50]);

        $this->post(route('admin.certificats.store'), [
            'formation_id' => $f->id,
            'date_debut' => '2026-08-01', 'date_fin' => '2026-09-30',
            'titulaire_nom' => 'Moukala', 'titulaire_prenom' => 'Clarisse', 'titulaire_pays' => 'Congo',
            'titulaire_date_naissance' => '1998-04-12', 'titulaire_lieu_naissance' => 'Brazzaville',
            'photo' => UploadedFile::fake()->image('photo.jpg', 200, 200),
        ])->assertRedirect(route('admin.certificats.index'))->assertSessionHas('success');

        $c = Certificat::firstOrFail();
        $this->assertSame(Certificat::MODE_PRESENTIEL, $c->mode);
        $this->assertNull($c->user_id);
        $this->assertSame('MOUKALA', $c->titulaire_nom);
        $this->assertSame(50, $c->duree_heures);
        $this->assertTrue($c->is_public);
        $this->assertMatchesRegularExpression('/^CFF-\d{4}-ACLOG-CG-\d{3}CG$/', $c->numero);
        Storage::disk('public')->assertExists($c->titulaire_photo);

        $this->get('/a-propos')->assertOk()->assertSee('MOUKALA')->assertSee('Clarisse')->assertSee('Douane Presentiel')
            ->assertSee('storage/' . $c->titulaire_photo, false)->assertDontSee('La liste des étudiants certifiés sera publiée ici');
        $this->get('/annuaire-certifies?q=Clarisse')->assertOk()->assertSee('MOUKALA');
        $this->get(route('certificat.verifier', $c->code_verification))->assertOk()
            ->assertSee('Certificat Authentique')->assertSee('Clarisse MOUKALA')->assertSee('En présentiel');

        $pdf = $this->get(route('admin.certificats.pdf', $c));
        $pdf->assertOk();
        $this->assertSame('application/pdf', $pdf->headers->get('content-type'));
    }

    public function test_free_title_is_accepted_when_formation_is_not_on_the_platform(): void
    {
        $this->actingAs($this->admin());

        $this->post(route('admin.certificats.store'), ['formation_titre' => 'Gestion des stocks', 'titulaire_nom' => 'Samba'])
            ->assertSessionHasNoErrors();

        $this->assertSame('Gestion des stocks', Certificat::firstOrFail()->intitule_formation);
    }

    public function test_a_formation_is_required(): void
    {
        $this->actingAs($this->admin());

        $this->post(route('admin.certificats.store'), ['titulaire_nom' => 'Samba'])
            ->assertSessionHasErrors(['formation_id', 'formation_titre']);
        $this->assertSame(0, Certificat::count());
    }

    public function test_bulk_generation_creates_one_certificate_per_line_with_sequential_numbers(): void
    {
        $this->actingAs($this->gestionnaire());
        $f = $this->formation();

        $this->post(route('admin.certificats.lot'), [
            'formation_id' => $f->id,
            'liste' => "MOUKALA ; Clarisse ; Congo ; 12/04/1998 ; Brazzaville\nSamba\tJean-Paul\tcongo (rdc)\n\n  NGUESSO;Cedric;Cameroun",
        ])->assertSessionHasNoErrors()->assertSessionHas('success');

        $certs = Certificat::orderBy('id')->get();
        $this->assertCount(3, $certs);
        $this->assertSame(['MOUKALA', 'SAMBA', 'NGUESSO'], $certs->pluck('titulaire_nom')->all());
        $this->assertSame('Congo (RDC)', $certs[1]->titulaire_pays);
        $this->assertSame('1998-04-12', $certs[0]->titulaire_date_naissance->format('Y-m-d'));
        $this->assertStringEndsWith('-001CG', $certs[0]->numero);
        $this->assertStringEndsWith('-002CD', $certs[1]->numero);
        $this->assertStringEndsWith('-003CM', $certs[2]->numero);
    }

    public function test_bulk_generation_rejects_bad_dates_without_creating_anything(): void
    {
        $this->actingAs($this->admin());
        $f = $this->formation();

        $this->post(route('admin.certificats.lot'), ['formation_id' => $f->id, 'liste' => "A;B;Congo;12/04/1998\nC;D;Congo;31-31-2000"])
            ->assertSessionHasErrors('liste');
        $this->assertSame(0, Certificat::count());
    }

    public function test_presentiel_certificate_can_be_edited_hidden_and_deleted(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);
        $f = $this->formation();
        $this->post(route('admin.certificats.store'), ['formation_id' => $f->id, 'titulaire_nom' => 'Avant']);
        $c = Certificat::firstOrFail();

        $this->get(route('admin.certificats.edit', $c))->assertOk()->assertSee('AVANT');
        $this->put(route('admin.certificats.update', $c), [
            'formation_id' => $f->id, 'titulaire_nom' => 'Apres',
            'photo' => UploadedFile::fake()->image('p.png'),
        ])->assertRedirect(route('admin.certificats.index'));
        $this->assertSame('APRES', $c->fresh()->titulaire_nom);
        $this->assertNotNull($c->fresh()->titulaire_photo);

        $this->patch(route('admin.certificats.visibilite', $c));
        $this->assertFalse($c->fresh()->is_public);
        $this->get('/a-propos')->assertDontSee('APRES');
        $this->get(route('certificat.verifier', $c->code_verification))->assertSee('Certificat Authentique');

        $this->delete(route('admin.certificats.destroy', $c))->assertSessionHas('success');
        $this->assertSame(0, Certificat::count());
    }

    public function test_online_certificates_cannot_be_edited_or_deleted_from_presentiel_screen(): void
    {
        $this->actingAs($this->admin());
        $f = $this->formation();
        $c = app(\App\Services\CertificatService::class)->generer($this->apprenant(), $f, 90);

        $this->get(route('admin.certificats.edit', $c))->assertNotFound();
        $this->delete(route('admin.certificats.destroy', $c))->assertForbidden();
        $this->get(route('admin.certificats.index'))->assertOk()->assertSee($c->numero);
    }

    public function test_gestionnaire_cannot_delete_and_learner_cannot_access(): void
    {
        $this->actingAs($this->admin());
        $this->post(route('admin.certificats.store'), ['formation_titre' => 'X', 'titulaire_nom' => 'Y']);
        $c = Certificat::firstOrFail();

        $this->actingAs($this->gestionnaire())->delete(route('admin.certificats.destroy', $c))->assertForbidden();
        $this->actingAs($this->apprenant())->get(route('admin.certificats.index'))->assertForbidden();
        $this->assertSame(1, Certificat::count());
    }
}
