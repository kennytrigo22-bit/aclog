<?php

namespace Tests\Feature\Admin;

use App\Models\Challenge;
use App\Models\CodePromo;
use App\Models\Parametre;
use App\Models\Parcours;
use App\Models\Partenaire;
use App\Models\Question;
use App\Models\TestNiveau;
use App\Models\TestNiveauResultat;
use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MiscAdminTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->actingAs($this->admin());
    }

    // ───────── Promos ─────────

    private function promoPayload(array $over = []): array
    {
        return array_merge(['code' => 'ETE2026', 'type' => 'pourcentage', 'valeur' => 15, 'perimetre' => 'all', 'is_active' => 1], $over);
    }

    public function test_promo_crud(): void
    {
        $this->get(route('admin.promos.create'))->assertOk();
        $this->post(route('admin.promos.store'), $this->promoPayload())->assertRedirect(route('admin.promos.index'));
        $promo = CodePromo::firstOrFail();
        $this->assertTrue($promo->is_active);
        $this->assertNotNull($promo->created_by);
        $this->get(route('admin.promos.index'))->assertOk()->assertSee('ETE2026');
        $this->get(route('admin.promos.edit', $promo))->assertOk();

        $this->put(route('admin.promos.update', $promo), $this->promoPayload(['valeur' => 30, 'is_active' => 0]))->assertRedirect(route('admin.promos.index'));
        $promo->refresh();
        $this->assertSame(30, $promo->valeur);
        $this->assertFalse($promo->is_active);

        $this->delete(route('admin.promos.destroy', $promo))->assertRedirect();
        $this->assertSame(0, CodePromo::count());
    }

    public function test_promo_perimetre_values_and_validation(): void
    {
        $f = $this->formation();
        foreach (['all', 'formation', 'categorie'] as $i => $p) {
            $this->post(route('admin.promos.store'), $this->promoPayload(['code' => "P{$i}", 'perimetre' => $p, 'formation_id' => $f->id, 'categorie' => 'logistique']))->assertSessionHasNoErrors();
        }
        $this->assertSame(3, CodePromo::count());
        $this->post(route('admin.promos.store'), $this->promoPayload(['code' => 'BAD', 'perimetre' => 'partout']))->assertSessionHasErrors('perimetre');
        $this->post(route('admin.promos.store'), $this->promoPayload(['code' => 'P0']))->assertSessionHasErrors('code');
        $this->post(route('admin.promos.store'), $this->promoPayload(['code' => 'T', 'type' => 'cadeau']))->assertSessionHasErrors('type');
        $this->post(route('admin.promos.store'), $this->promoPayload(['code' => 'V', 'valeur' => -5]))->assertSessionHasErrors('valeur');
        $this->post(route('admin.promos.store'), $this->promoPayload(['code' => 'D', 'date_debut' => '2026-05-10', 'date_fin' => '2026-05-01']))->assertSessionHasErrors('date_fin');
        $this->post(route('admin.promos.store'), $this->promoPayload(['code' => 'F', 'formation_id' => 99999]))->assertSessionHasErrors('formation_id');
        $this->post(route('admin.promos.store'), $this->promoPayload(['code' => 'M', 'nb_utilisations_max' => 0]))->assertSessionHasErrors('nb_utilisations_max');
        $this->post(route('admin.promos.store'), [])->assertSessionHasErrors(['code', 'type', 'valeur', 'perimetre']);
    }

    public function test_promo_percentage_is_capped_at_100(): void
    {
        // FINDING : pas de max sur valeur pour type pourcentage.
        $this->post(route('admin.promos.store'), $this->promoPayload(['code' => 'OVER', 'valeur' => 250]))->assertSessionHasErrors('valeur');
    }

    public function test_promo_scoped_to_formation_requires_formation(): void
    {
        $this->post(route('admin.promos.store'), $this->promoPayload(['code' => 'NOFORM', 'perimetre' => 'formation']))->assertSessionHasErrors('formation_id');
    }

    public function test_promo_code_is_escaped_in_list(): void
    {
        $this->promo(['code' => '<b>X</b>']);
        $this->get(route('admin.promos.index'))->assertOk()->assertDontSee('<b>X</b>', false);
    }

    // ───────── Partenaires ─────────

    public function test_partner_store_toggle_delete(): void
    {
        $this->get(route('admin.partenaires.index'))->assertOk();
        $this->post(route('admin.partenaires.store'), ['nom' => 'ACME', 'logo' => $this->realUpload('logo.png', $this->pngBytes())])->assertSessionHasNoErrors();
        $p = Partenaire::firstOrFail();
        $this->assertTrue($p->actif);
        Storage::disk('public')->assertExists($p->logo);

        $this->patch(route('admin.partenaires.toggle', $p))->assertRedirect();
        $this->assertFalse($p->fresh()->actif);
        $this->get('/')->assertOk()->assertDontSee('ACME');
        $this->patch(route('admin.partenaires.toggle', $p))->assertRedirect();
        $this->assertTrue($p->fresh()->actif);

        $logo = $p->logo;
        $this->delete(route('admin.partenaires.destroy', $p))->assertRedirect();
        $this->assertSame(0, Partenaire::count());
        Storage::disk('public')->assertMissing($logo);
    }

    public function test_partner_logo_rejects_dangerous_files_and_requires_fields(): void
    {
        foreach ($this->disguisedFiles('png') as $k => $file) {
            $this->post(route('admin.partenaires.store'), ['nom' => 'Bad', 'logo' => $file])->assertSessionHasErrors('logo');
        }
        $this->post(route('admin.partenaires.store'), ['nom' => '', 'logo' => $this->realUpload('l.png', $this->pngBytes())])->assertSessionHasErrors('nom');
        $this->post(route('admin.partenaires.store'), ['nom' => 'X'])->assertSessionHasErrors('logo');
        $this->assertSame(0, Partenaire::count());
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_active_partner_is_shown_on_home_with_escaped_name(): void
    {
        Partenaire::create(['nom' => '<i>Evil</i>', 'logo' => 'partenaires/x.png', 'ordre' => 1, 'actif' => true]);
        $this->get('/')->assertOk()->assertDontSee('<i>Evil</i>', false);
    }

    // ───────── Parametres ─────────

    public function test_parametres_update_is_reflected_on_public_footer(): void
    {
        Parametre::definir('site_titre', 'ANCIEN TITRE');
        // NB: on ne rend aucune vue avant la mise a jour : AppServiceProvider memorise l'identite dans une variable
        // `static` du view composer (obsolete des la 2e requete d'un meme process, ex. Octane).
        $this->post(route('admin.parametres.update'), ['parametres' => [
            ['cle' => 'site_titre', 'valeur' => 'NOUVEAU TITRE'],
            ['cle' => 'site_slogan', 'valeur' => 'Nouveau slogan'],
            ['cle' => 'legal_rccm', 'valeur' => 'RCCM-NEW'],
            ['cle' => 'legal_scien', 'valeur' => 'SCIEN-NEW'],
            ['cle' => 'legal_sciet', 'valeur' => 'SCIET-NEW'],
            ['cle' => 'legal_niu', 'valeur' => 'NIU-NEW'],
        ]])->assertSessionHas('success');

        $this->get(route('admin.parametres'))->assertOk();
        $this->post('/logout');
        $this->get('/')->assertOk()->assertSee('NOUVEAU TITRE')->assertSee('Nouveau slogan')
            ->assertSee('RCCM-NEW')->assertSee('SCIEN-NEW')->assertSee('SCIET-NEW')->assertSee('NIU-NEW')->assertDontSee('ANCIEN TITRE');
        $this->get('/mentions-legales')->assertOk();
    }

    public function test_parametres_validation(): void
    {
        $this->post(route('admin.parametres.update'), [])->assertSessionHasErrors('parametres');
        $this->post(route('admin.parametres.update'), ['parametres' => [['valeur' => 'x']]])->assertSessionHasErrors('parametres.0.cle');
    }

    public function test_parametres_values_are_escaped_on_public_pages(): void
    {
        $this->post(route('admin.parametres.update'), ['parametres' => [['cle' => 'site_titre', 'valeur' => '<script>alert(1)</script>']]]);
        $this->get('/')->assertOk()->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_parametre_change_applies_immediately_despite_cache(): void
    {
        $this->seedParametres(['score_minimum_quiz' => 70]);
        $this->assertSame('70', Parametre::valeur('score_minimum_quiz'));
        $this->post(route('admin.parametres.update'), ['parametres' => [['cle' => 'score_minimum_quiz', 'valeur' => '55']]]);
        $this->assertSame('55', Parametre::valeur('score_minimum_quiz'));
    }

    // ───────── Tickets ─────────

    public function test_ticket_list_show_reply_and_status(): void
    {
        $learner = $this->apprenant(['name' => 'Demandeur']);
        $t = $this->ticket($learner, ['sujet' => 'Souci acces']);
        $this->get(route('admin.tickets.index'))->assertOk()->assertSee('Souci acces');
        $this->get(route('admin.tickets.index', ['statut' => 'ferme']))->assertOk()->assertDontSee('Souci acces');
        $this->get(route('admin.tickets.show', $t))->assertOk()->assertSee('Souci acces');

        $this->post(route('admin.tickets.repondre', $t), [])->assertSessionHasErrors('message');
        $this->post(route('admin.tickets.repondre', $t), ['message' => 'Regle'])->assertSessionHas('success');
        $this->assertTrue((bool) TicketMessage::first()->is_admin);
        $this->assertSame('en_cours', $t->fresh()->statut);

        $this->patch(route('admin.tickets.statut', $t), ['statut' => 'ferme'])->assertSessionHas('success');
        $this->assertSame('ferme', $t->fresh()->statut);
        $this->patch(route('admin.tickets.statut', $t), ['statut' => 'nimporte'])->assertSessionHasErrors('statut');
    }

    public function test_every_status_accepted_by_validation_is_storable(): void
    {
        // FINDING : la validation accepte "resolu" mais l'enum SQL (ouvert,en_cours,ferme) le refuse -> 500.
        $t = $this->ticket($this->apprenant());
        $res = $this->patch(route('admin.tickets.statut', $t), ['statut' => 'resolu']);
        $this->assertLessThan(500, $res->status(), 'statut=resolu => erreur SQL');
    }

    // ───────── Challenges admin ─────────

    public function test_challenge_admin_index_and_forms(): void
    {
        $p = $this->parcours(['titre' => 'Parcours Admin Idx']);
        $this->get(route('admin.challenges.index'))->assertOk()->assertSee('Parcours Admin Idx');
        $this->get(route('admin.challenges.parcours.create'))->assertOk();
        $this->get(route('admin.challenges.parcours.edit', $p))->assertOk();
        $this->get(route('admin.challenges.parcours.show', $p))->assertOk();
    }

    public function test_parcours_can_be_created_with_levels_offered_by_the_form(): void
    {
        // FINDING : le formulaire propose facile|moyen|difficile (valides cote Laravel) mais la colonne SQL est
        // enum(debutant,intermediaire,avance) -> erreur 500 a l'enregistrement.
        foreach (['facile', 'moyen', 'difficile'] as $niveau) {
            $res = $this->post(route('admin.challenges.parcours.store'), [
                'titre' => "Parcours {$niveau}", 'domaine' => 'logistique', 'niveau' => $niveau, 'is_active' => 1,
            ]);
            $this->assertLessThan(500, $res->status(), "niveau={$niveau} => 500");
        }
    }

    public function test_parcours_store_update_delete_with_db_valid_levels(): void
    {
        $this->post(route('admin.challenges.parcours.store'), ['titre' => 'P', 'domaine' => 'logistique', 'niveau' => 'debutant']);
        $this->assertSame(1, Parcours::where('titre', 'P')->count());
    }

    public function test_parcours_validation_and_image_hardening(): void
    {
        $this->post(route('admin.challenges.parcours.store'), [])->assertSessionHasErrors(['titre', 'domaine', 'niveau']);
        $this->post(route('admin.challenges.parcours.store'), ['titre' => 'T', 'domaine' => 'logistique', 'niveau' => 'galactique'])->assertSessionHasErrors('niveau');
        foreach ($this->disguisedFiles('jpg') as $file) {
            $this->post(route('admin.challenges.parcours.store'), ['titre' => 'T', 'domaine' => 'logistique', 'niveau' => 'facile', 'image' => $file])->assertSessionHasErrors('image');
        }
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_challenge_store_generates_flag_and_enforces_min_questions(): void
    {
        $p = $this->parcours();
        $payload = ['titre' => 'Blocage Douanier', 'theme' => 'douane', 'points' => 5, 'nb_questions' => 10, 'is_active' => 1];
        $res = $this->post(route('admin.challenges.store', $p), $payload);
        $c = Challenge::firstOrFail();
        $res->assertRedirect(route('admin.qcm-import.index', ['cible' => 'etape:' . $c->id]));
        $this->assertMatchesRegularExpression('/^ACLOG\{BLOCAGE_DOUANIER_[A-Z0-9]{5}\}$/', $c->flag_attendu);
        $this->assertSame(1, $c->ordre);

        $this->post(route('admin.challenges.store', $p), array_merge($payload, ['nb_questions' => 9]))->assertSessionHasErrors('nb_questions');
        $this->post(route('admin.challenges.store', $p), array_merge($payload, ['nb_questions' => 51]))->assertSessionHasErrors('nb_questions');
        $this->post(route('admin.challenges.store', $p), array_merge($payload, ['points' => 0]))->assertSessionHasErrors('points');
        $this->post(route('admin.challenges.store', $p), array_merge($payload, ['titre' => 'Second']));
        $this->assertSame([1, 2], $p->challenges()->pluck('ordre')->all());
    }

    public function test_challenge_update_keeps_flag_and_delete_cascades_questions(): void
    {
        $p = $this->parcours();
        $c = $this->challenge($p, 1, 10, ['flag_attendu' => 'ACLOG{KEEP_ME}']);
        $this->put(route('admin.challenges.challenge.update', $c), ['titre' => 'Renomme', 'points' => 8, 'nb_questions' => 12, 'is_active' => 1])->assertSessionHasNoErrors();
        $c->refresh();
        $this->assertSame('Renomme', $c->titre);
        $this->assertSame('ACLOG{KEEP_ME}', $c->flag_attendu);

        $this->assertSame(10, Question::count());
        $this->delete(route('admin.challenges.challenge.destroy', $c))->assertRedirect();
        $this->assertSame(0, Challenge::count());
        $this->assertSame(0, Question::count());
    }

    public function test_parcours_delete_cascades_steps_and_questions(): void
    {
        $p = $this->parcours();
        $this->challenge($p, 1);
        $this->challenge($p, 2);
        $this->delete(route('admin.challenges.parcours.destroy', $p))->assertRedirect(route('admin.challenges.index'));
        $this->assertSame(0, Parcours::count());
        $this->assertSame(0, Challenge::count());
        $this->assertSame(0, Question::count());
    }

    public function test_admin_challenge_page_does_not_leak_nothing_to_public_but_shows_flag_to_staff(): void
    {
        $p = $this->parcours();
        $c = $this->challenge($p, 1, 10, ['flag_attendu' => 'ACLOG{SECRET_STAFF_ONLY}']);
        $this->post('/logout');
        // La page apprenant ne doit jamais afficher le flag attendu.
        $learner = $this->apprenant();
        $this->actingAs($learner)->get(route('apprenant.challenges.parcours', $p))->assertDontSee('ACLOG{SECRET_STAFF_ONLY}', false);
        $this->get(route('apprenant.challenges.etape', $c))->assertDontSee('ACLOG{SECRET_STAFF_ONLY}', false);
    }

    // ───────── Tests de niveau admin ─────────

    public function test_test_niveau_index_autocreates_15_combinations(): void
    {
        $this->assertSame(0, TestNiveau::count());
        $this->get(route('admin.test-niveau.index'))->assertOk();
        $this->assertSame(15, TestNiveau::count());
        $this->assertSame(5, TestNiveau::distinct('domaine')->count('domaine'));
        $this->assertSame(3, TestNiveau::distinct('niveau')->count('niveau'));
        $this->get(route('admin.test-niveau.index'))->assertOk();
        $this->assertSame(15, TestNiveau::count());
    }

    public function test_test_niveau_question_add_and_delete(): void
    {
        $this->get(route('admin.test-niveau.index'));
        $t = TestNiveau::first();
        $this->post(route('admin.test-niveau.questions.store'), [
            'test_niveau_id' => $t->id, 'texte' => 'Q niveau ?', 'type' => 'qcm',
            'reponses' => [['texte' => 'A', 'is_correct' => 1], ['texte' => 'B']],
        ])->assertSessionHasNoErrors();
        $this->assertSame(1, $t->questions()->count());
        $this->post(route('admin.test-niveau.questions.store'), ['test_niveau_id' => 9999, 'texte' => 'x', 'type' => 'qcm', 'reponses' => [['texte' => 'a'], ['texte' => 'b']]])->assertSessionHasErrors('test_niveau_id');
        $this->post(route('admin.test-niveau.questions.store'), ['test_niveau_id' => $t->id, 'texte' => 'x', 'type' => 'qcm', 'reponses' => [['texte' => 'a']]])->assertSessionHasErrors('reponses');

        $q = $t->questions()->first();
        $this->delete(route('admin.test-niveau.questions.destroy', $q))->assertRedirect();
        $this->assertSame(0, Question::count());
    }

    public function test_test_niveau_results_page(): void
    {
        $u = $this->apprenant(['name' => 'ResultatUser']);
        TestNiveauResultat::create(['user_id' => $u->id, 'domaine' => 'douane', 'niveau' => 'avance', 'score' => 80, 'reponses_json' => [], 'passed' => true, 'created_at' => now()]);
        TestNiveauResultat::create(['user_id' => null, 'domaine' => 'douane', 'niveau' => 'avance', 'score' => 10, 'reponses_json' => [], 'passed' => false, 'created_at' => now()]);
        $this->get(route('admin.test-niveau.resultats'))->assertOk()->assertSee('ResultatUser');
    }
}
