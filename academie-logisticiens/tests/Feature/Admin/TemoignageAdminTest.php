<?php

namespace Tests\Feature\Admin;

use App\Models\Temoignage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TemoignageAdminTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Temoignage::query()->delete();
    }

    public function test_admin_adds_a_testimonial_that_appears_on_home(): void
    {
        $this->actingAs($this->admin());

        $this->post(route('admin.temoignages.store'), [
            'citation' => '« Formation très concrète, je recommande. »',
            'nom' => 'Marie Ngoma',
            'fonction' => 'Agent de transit • Pointe-Noire',
            'photo' => UploadedFile::fake()->image('m.jpg'),
        ])->assertRedirect(route('admin.parametres') . '#temoignages')->assertSessionHas('success');

        $t = Temoignage::firstOrFail();
        $this->assertSame('Formation très concrète, je recommande.', $t->citation);
        $this->assertTrue($t->actif);
        Storage::disk('public')->assertExists($t->photo);

        $this->get(route('admin.parametres'))->assertOk()->assertSee('Marie Ngoma')->assertSee('Ajouter un témoignage');
        $this->get('/')->assertOk()->assertSee('« Formation très concrète, je recommande. »', false)
            ->assertSee('Agent de transit • Pointe-Noire')->assertSee('storage/' . $t->photo, false);
    }

    public function test_initials_are_shown_without_photo(): void
    {
        $this->actingAs($this->admin());
        $this->post(route('admin.temoignages.store'), ['citation' => 'Top', 'nom' => 'Jean-Paul Samba']);

        $this->assertSame('JS', Temoignage::firstOrFail()->initiales);
        $this->get('/')->assertSee('<span class="av">JS</span>', false);
    }

    public function test_hidden_and_deleted_testimonials_leave_the_home_page(): void
    {
        $this->actingAs($this->admin());
        $this->post(route('admin.temoignages.store'), ['citation' => 'Citation masquable', 'nom' => 'Paul Mavoungou', 'photo' => UploadedFile::fake()->image('p.png')]);
        $t = Temoignage::firstOrFail();

        $this->patch(route('admin.temoignages.toggle', $t))->assertSessionHas('success');
        $this->assertFalse($t->fresh()->actif);
        $this->get('/')->assertDontSee('Citation masquable')->assertDontSee('Ils témoignent de Brazzaville');

        $this->patch(route('admin.temoignages.toggle', $t));
        $this->get('/')->assertSee('Citation masquable');

        $this->delete(route('admin.temoignages.destroy', $t))->assertSessionHas('success');
        $this->assertSame(0, Temoignage::count());
        Storage::disk('public')->assertMissing($t->photo);
    }

    public function test_validation(): void
    {
        $this->actingAs($this->admin());

        $this->post(route('admin.temoignages.store'), [])->assertSessionHasErrors(['citation', 'nom']);
        $this->post(route('admin.temoignages.store'), ['citation' => str_repeat('x', 601), 'nom' => 'A', 'photo' => UploadedFile::fake()->create('x.pdf', 10, 'application/pdf')])
            ->assertSessionHasErrors(['citation', 'photo']);
        $this->assertSame(0, Temoignage::count());
    }

    public function test_only_admins_manage_testimonials(): void
    {
        $t = Temoignage::create(['citation' => 'C', 'nom' => 'N']);

        $this->actingAs($this->gestionnaire())->post(route('admin.temoignages.store'), ['citation' => 'X', 'nom' => 'Y'])->assertForbidden();
        $this->actingAs($this->gestionnaire())->delete(route('admin.temoignages.destroy', $t))->assertForbidden();
        $this->actingAs($this->apprenant())->patch(route('admin.temoignages.toggle', $t))->assertForbidden();
        $this->assertSame(1, Temoignage::count());
    }
}
