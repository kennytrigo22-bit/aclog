<?php

namespace Tests\Feature\Learner;

use App\Models\FormationRessource;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RessourceTest extends TestCase
{
    private function ressource($formation, array $attrs = []): FormationRessource
    {
        return FormationRessource::create(array_merge([
            'formation_id' => $formation->id, 'titre' => 'Fiche entretien', 'type' => 'fiche_entretien',
            'contenu_html' => '<p>Contenu fiche</p>', 'ordre' => 1,
        ], $attrs));
    }

    public function test_enrolled_learner_can_view_resource(): void
    {
        $f = $this->formation();
        $r = $this->ressource($f);
        $u = $this->apprenant();
        $this->inscrire($u, $f);
        $this->actingAs($u)->get(route('apprenant.ressources.show', $r))->assertOk()->assertSee('Fiche entretien');
        $this->inscrire($u, $f, 'terminee');
        $this->get(route('apprenant.ressources.show', $r))->assertOk();
    }

    public function test_non_enrolled_or_pending_learner_gets_403_on_every_resource_route(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('ressources/x.pdf', '%PDF-1.4');
        $f = $this->formation();
        $r = $this->ressource($f, ['fichier' => 'ressources/x.pdf', 'nom_fichier' => 'x.pdf', 'mime' => 'application/pdf']);
        $stranger = $this->apprenant();
        $pending = $this->apprenant();
        $this->inscrire($pending, $f, 'en_attente');
        $otherFormation = $this->formation();
        $enrolledElsewhere = $this->apprenant();
        $this->inscrire($enrolledElsewhere, $otherFormation);

        foreach ([$stranger, $pending, $enrolledElsewhere] as $u) {
            $this->actingAs($u);
            $this->get(route('apprenant.ressources.show', $r))->assertForbidden();
            $this->get(route('apprenant.ressources.telecharger', $r))->assertForbidden();
            $this->get(route('apprenant.ressources.pdf', $r))->assertForbidden();
        }
        auth()->logout();
        $this->get(route('apprenant.ressources.show', $r))->assertRedirect(route('login'));
    }

    public function test_enrolled_learner_downloads_and_views_pdf_with_safe_headers(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('ressources/x.pdf', '%PDF-1.4 test');
        $f = $this->formation();
        $r = $this->ressource($f, ['fichier' => 'ressources/x.pdf', 'nom_fichier' => 'guide.pdf', 'mime' => 'application/pdf']);
        $u = $this->apprenant();
        $this->inscrire($u, $f);

        $dl = $this->actingAs($u)->get(route('apprenant.ressources.telecharger', $r))->assertOk();
        $this->assertSame('nosniff', $dl->headers->get('X-Content-Type-Options'));
        $this->assertStringContainsString('guide.pdf', $dl->headers->get('Content-Disposition'));

        $inline = $this->get(route('apprenant.ressources.pdf', $r))->assertOk();
        $this->assertStringContainsString('application/pdf', $inline->headers->get('Content-Type'));
        $this->assertStringContainsString('inline', $inline->headers->get('Content-Disposition'));
    }

    public function test_missing_file_and_non_pdf_inline_are_404(): void
    {
        Storage::fake('local');
        $f = $this->formation();
        $u = $this->apprenant();
        $this->inscrire($u, $f);
        $gone = $this->ressource($f, ['fichier' => 'ressources/absent.pdf', 'mime' => 'application/pdf']);
        $noFile = $this->ressource($f);
        Storage::disk('local')->put('ressources/doc.docx', 'x');
        $docx = $this->ressource($f, ['fichier' => 'ressources/doc.docx', 'mime' => 'application/msword']);
        $this->actingAs($u);
        $this->get(route('apprenant.ressources.telecharger', $gone))->assertNotFound();
        $this->get(route('apprenant.ressources.telecharger', $noFile))->assertNotFound();
        $this->get(route('apprenant.ressources.pdf', $docx))->assertNotFound();
    }

    public function test_admin_can_open_any_resource_and_unknown_id_is_404(): void
    {
        $r = $this->ressource($this->formation());
        $this->actingAs($this->admin())->get(route('apprenant.ressources.show', $r))->assertOk();
        $this->get('/espace/ressources/99999')->assertNotFound();
    }

    public function test_resource_html_with_script_is_not_rendered_raw(): void
    {
        // FINDING (meme famille que la XSS des lecons) : ressources/show.blade.php:27 -> {!! contenu_html !!}
        $f = $this->formation();
        $r = $this->ressource($f, ['contenu_html' => '<p>ok</p><script>alert("res")</script>']);
        $u = $this->apprenant();
        $this->inscrire($u, $f);
        $res = $this->actingAs($u)->get(route('apprenant.ressources.show', $r))->assertOk();
        $this->assertStringNotContainsString('<script>alert("res")</script>', $res->getContent());
    }

    public function test_deleting_resource_removes_file_from_disk(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('ressources/y.pdf', 'x');
        $r = $this->ressource($this->formation(), ['fichier' => 'ressources/y.pdf']);
        $r->delete();
        Storage::disk('local')->assertMissing('ressources/y.pdf');
    }
}
