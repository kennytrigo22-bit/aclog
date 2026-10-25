<?php

namespace Tests\Feature\Learner;

use Tests\TestCase;

class CertificatAccessTest extends TestCase
{
    public function test_owner_can_download_pdf(): void
    {
        $f = $this->formation(['titre' => 'Formation PDF']);
        $u = $this->apprenant();
        $this->inscrire($u, $f, 'terminee');
        $c = $this->certificat($u, $f);

        $res = $this->actingAs($u)->get(route('apprenant.certificats.download', $c))->assertOk();
        $this->assertStringContainsString('application/pdf', $res->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF', $res->getContent() ?: $res->streamedContent());
        $this->assertStringContainsString($c->code_verification, $res->headers->get('Content-Disposition'));
    }

    public function test_admin_can_download_any_certificate(): void
    {
        $f = $this->formation();
        $c = $this->certificat($this->apprenant(), $f);
        $this->actingAs($this->admin())->get(route('apprenant.certificats.download', $c))->assertOk();
    }

    public function test_other_user_cannot_download_certificate(): void
    {
        $f = $this->formation();
        $c = $this->certificat($this->apprenant(), $f);
        $this->actingAs($this->apprenant())->get(route('apprenant.certificats.download', $c))->assertForbidden();
    }

    public function test_gestionnaire_cannot_use_learner_certificate_routes(): void
    {
        $f = $this->formation();
        $c = $this->certificat($this->apprenant(), $f);
        $this->actingAs($this->gestionnaire())->get(route('apprenant.certificats.download', $c))->assertForbidden();
    }

    public function test_guest_redirected_and_unknown_id_404(): void
    {
        $f = $this->formation();
        $c = $this->certificat($this->apprenant(), $f);
        $this->get(route('apprenant.certificats.download', $c))->assertRedirect(route('login'));
        $this->actingAs($this->apprenant())->get('/espace/certifications/99999/download')->assertNotFound();
    }

    public function test_index_lists_only_own_certificates(): void
    {
        $f = $this->formation(['titre' => 'Cert Formation']);
        $f2 = $this->formation(['titre' => 'Cert Autrui']);
        $me = $this->apprenant();
        $this->certificat($me, $f);
        $this->certificat($this->apprenant(), $f2);
        $this->actingAs($me)->get('/espace/certifications')->assertOk()->assertSee('Cert Formation')->assertDontSee('Cert Autrui');
    }

    public function test_visibility_toggle_by_owner_only(): void
    {
        $f = $this->formation();
        $me = $this->apprenant();
        $c = $this->certificat($me, $f, ['is_public' => false]);

        $this->actingAs($this->apprenant())->patch(route('apprenant.certificats.visibilite', $c))->assertForbidden();
        $this->assertFalse((bool) $c->fresh()->is_public);

        $this->actingAs($me)->patch(route('apprenant.certificats.visibilite', $c))->assertRedirect();
        $this->assertTrue((bool) $c->fresh()->is_public);
        $this->patch(route('apprenant.certificats.visibilite', $c))->assertRedirect();
        $this->assertFalse((bool) $c->fresh()->is_public);
    }

    public function test_admin_cannot_toggle_someone_elses_visibility(): void
    {
        $c = $this->certificat($this->apprenant(), $this->formation());
        $this->actingAs($this->admin())->patch(route('apprenant.certificats.visibilite', $c))->assertForbidden();
    }
}
