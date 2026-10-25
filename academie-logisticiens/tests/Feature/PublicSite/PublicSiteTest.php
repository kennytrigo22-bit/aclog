<?php

namespace Tests\Feature\Public;

use App\Models\Parametre;
use App\Models\TestNiveau;
use App\Models\TestNiveauResultat;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    public function test_home_renders_with_featured_formations_only_published(): void
    {
        $this->formation(['titre' => 'Vedette Publiee', 'is_featured' => true]);
        $this->unpublishedFormation(['titre' => 'Vedette Cachee', 'is_featured' => true]);

        $this->get('/')->assertOk()->assertSee('Vedette Publiee')->assertDontSee('Vedette Cachee');
    }

    public function test_formations_index_lists_only_published_and_filters(): void
    {
        $this->formation(['titre' => 'Log Debutant', 'categorie' => 'logistique', 'niveau' => 'debutant']);
        $this->formation(['titre' => 'Douane Avance', 'categorie' => 'douane', 'niveau' => 'avance']);
        $this->unpublishedFormation(['titre' => 'Brouillon Secret']);

        $this->get('/formations')->assertOk()->assertSee('Log Debutant')->assertSee('Douane Avance')->assertDontSee('Brouillon Secret');
        $this->get('/formations?categorie=douane')->assertOk()->assertSee('Douane Avance')->assertDontSee('Log Debutant');
        $this->get('/formations?niveau=debutant')->assertOk()->assertSee('Log Debutant')->assertDontSee('Douane Avance');
    }

    public function test_formation_show_displays_plan_for_published(): void
    {
        $f = $this->formation(['titre' => 'Ma Formation']);
        $m = $this->module($f, ['titre' => 'Module Visible Plan']);
        $this->lecon($m, ['titre' => 'Lecon Visible Plan']);

        $this->get("/formations/{$f->slug}")->assertOk()->assertSee('Module Visible Plan');
    }

    public function test_unpublished_formation_show_is_404(): void
    {
        $f = $this->unpublishedFormation();
        $this->get("/formations/{$f->slug}")->assertNotFound();
        $this->get('/formations/inexistante')->assertNotFound();
    }

    public function test_static_pages_render(): void
    {
        foreach (['/a-propos', '/contact', '/conditions-generales', '/politique-confidentialite', '/mentions-legales', '/tests-de-niveau', '/annuaire-certifies'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_404_page_is_custom(): void
    {
        $this->get('/page-qui-nexiste-pas')->assertNotFound()->assertSee('Page introuvable');
    }

    public function test_footer_shows_legal_identifiers_and_site_title(): void
    {
        Parametre::definir('site_titre', 'MON CABINET TEST');
        Parametre::definir('site_slogan', 'Un slogan unique');
        Parametre::definir('legal_rccm', 'RCCM-XYZ-1');
        Parametre::definir('legal_scien', 'SCIEN-22');
        Parametre::definir('legal_sciet', 'SCIET-33');
        Parametre::definir('legal_niu', 'NIU-44');

        $this->get('/')->assertOk()
            ->assertSee('MON CABINET TEST')->assertSee('Un slogan unique')
            ->assertSee('RCCM')->assertSee('RCCM-XYZ-1')
            ->assertSee('SCIEN-22')->assertSee('SCIET-33')->assertSee('NIU-44');
    }

    public function test_footer_defaults_without_parametres(): void
    {
        $this->get('/')->assertOk()->assertSee('CABINET ACLOG')->assertSee('RCCM')->assertSee('SCIEN')->assertSee('SCIET')->assertSee('NIU');
    }

    // ───────── Contact ─────────

    public function test_contact_form_sends_mail(): void
    {
        Mail::fake();
        $this->post('/contact', ['nom' => 'Jean', 'email' => 'jean@example.test', 'objet' => 'Hello', 'message' => 'Bonjour'])
            ->assertSessionHas('success');
    }

    public function test_contact_form_validation(): void
    {
        $this->post('/contact', [])->assertSessionHasErrors(['nom', 'email', 'objet', 'message']);
        $this->post('/contact', ['nom' => 'a', 'email' => 'pas-un-mail', 'objet' => 'b', 'message' => str_repeat('x', 5001)])
            ->assertSessionHasErrors(['email', 'message']);
    }

    public function test_contact_form_is_throttled_to_5_per_minute(): void
    {
        Mail::fake();
        $payload = ['nom' => 'Jean', 'email' => 'jean@example.test', 'objet' => 'Hello', 'message' => 'Bonjour'];
        for ($i = 0; $i < 5; $i++) {
            $this->post('/contact', $payload)->assertSessionHas('success');
        }
        $this->post('/contact', $payload)->assertStatus(429);
    }

    // ───────── Annuaire / certificat ─────────

    public function test_annuaire_lists_only_public_certificates(): void
    {
        $f = $this->formation();
        $pub = $this->apprenant(['name' => 'PublicPerson']);
        $priv = $this->apprenant(['name' => 'PrivatePerson']);
        $this->certificat($pub, $f, ['is_public' => true]);
        $this->certificat($priv, $f, ['is_public' => false]);

        $this->get('/annuaire-certifies')->assertOk()->assertSee('PublicPerson')->assertDontSee('PrivatePerson');
        $this->get('/annuaire-certifies?q=Public')->assertOk()->assertSee('PublicPerson');
        $this->get('/annuaire-certifies?q=Private')->assertOk()->assertDontSee('PrivatePerson');
    }

    public function test_certificate_verification_valid_and_invalid(): void
    {
        $f = $this->formation(['titre' => 'Formation Certifiee']);
        $u = $this->apprenant(['name' => 'Titulaire']);
        $this->certificat($u, $f, ['code_verification' => 'ABCD1234-EFGH5678']);

        $this->get('/certificat/verifier/ABCD1234-EFGH5678')->assertOk()->assertSee('Certificat Authentique')->assertSee('Formation Certifiee');
        $this->get('/certificat/verifier/NOPE')->assertOk()->assertSee('Certificat non trouvé');
    }

    public function test_certificate_verification_escapes_code(): void
    {
        $this->get('/certificat/verifier/' . rawurlencode('<script>alert(1)</script>'))
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    // ───────── Chatbot ─────────

    public function test_chatbot_answers_and_validates(): void
    {
        $this->formation(['titre' => 'Formation Chatbot']);
        $this->postJson('/api/chatbot', ['question' => 'Quelles formations ?'])->assertOk()
            ->assertJsonPath('reponse', fn ($r) => str_contains($r, 'Formation Chatbot'));
        $this->postJson('/api/chatbot', ['question' => 'bonjour'])->assertOk()->assertJsonStructure(['reponse']);
        $this->postJson('/api/chatbot', [])->assertStatus(422);
        $this->postJson('/api/chatbot', ['question' => str_repeat('a', 501)])->assertStatus(422);
    }

    public function test_chatbot_does_not_list_unpublished_formations(): void
    {
        $this->unpublishedFormation(['titre' => 'Formation Secrete Chat']);
        $this->postJson('/api/chatbot', ['question' => 'formation'])->assertOk()->assertDontSee('Formation Secrete Chat');
    }

    public function test_chatbot_is_rate_limited(): void
    {
        $status = null;
        for ($i = 0; $i < 70; $i++) {
            $status = $this->postJson('/api/chatbot', ['question' => 'bonjour'])->status();
            if ($status === 429) {
                break;
            }
        }
        $this->assertSame(429, $status, 'FINDING: /api/chatbot has no throttle (routes/api.php).');
    }

    // ───────── Test de niveau ─────────

    private function niveauAvecQuestions(int $n = 6): TestNiveau
    {
        $t = TestNiveau::create(['domaine' => 'logistique', 'niveau' => 'debutant', 'titre' => 'T', 'nb_questions' => 4, 'is_active' => true]);
        $this->addQuestions($t, $n);

        return $t;
    }

    public function test_niveau_questions_do_not_leak_answers_and_respect_count(): void
    {
        $this->niveauAvecQuestions(6);
        $res = $this->postJson('/tests-de-niveau/questions', ['domaine' => 'logistique', 'niveau' => 'debutant'])->assertOk();
        $this->assertCount(4, $res->json('questions'));
        $this->assertStringNotContainsString('is_correct', $res->getContent());
        $this->assertSame(['id', 'texte'], array_keys($res->json('questions.0.reponses.0')));
    }

    public function test_niveau_questions_empty_and_validation(): void
    {
        $this->postJson('/tests-de-niveau/questions', ['domaine' => 'logistique', 'niveau' => 'debutant'])->assertOk()->assertJsonPath('questions', []);
        $this->postJson('/tests-de-niveau/questions', ['domaine' => 'hack', 'niveau' => 'debutant'])->assertStatus(422);
        $this->postJson('/tests-de-niveau/questions', ['domaine' => 'logistique', 'niveau' => 'x'])->assertStatus(422);
    }

    public function test_niveau_soumettre_grades_only_served_questions(): void
    {
        $this->seedParametres();
        $t = $this->niveauAvecQuestions(6);
        $this->postJson('/tests-de-niveau/questions', ['domaine' => 'logistique', 'niveau' => 'debutant']);
        $reponses = $this->reponsesPour($t->questions()->with('reponses')->get());

        $this->post('/tests-de-niveau/soumettre', ['domaine' => 'logistique', 'niveau' => 'debutant', 'reponses' => $reponses]);
        $row = TestNiveauResultat::first();
        $this->assertSame(100, $row->score);
        $this->assertTrue($row->passed);
        $this->assertSame(1, TestNiveauResultat::count());
    }

    public function test_niveau_result_page_renders(): void
    {
        $this->seedParametres();
        $t = $this->niveauAvecQuestions(4);
        $this->postJson('/tests-de-niveau/questions', ['domaine' => 'logistique', 'niveau' => 'debutant']);
        $rep = $this->reponsesPour($t->questions()->with('reponses')->get());
        // BUG attendu: la vue resultat.blade.php:39 lit $recommandation['message'] alors que OrientationIAService retourne 'explication'.
        $this->post('/tests-de-niveau/soumettre', ['domaine' => 'logistique', 'niveau' => 'debutant', 'reponses' => $rep])->assertOk();
    }

    public function test_niveau_soumettre_without_served_questions_redirects_and_cannot_be_gamed(): void
    {
        $t = $this->niveauAvecQuestions(6);
        $rep = $this->reponsesPour($t->questions()->with('reponses')->get());
        $this->post('/tests-de-niveau/soumettre', ['domaine' => 'logistique', 'niveau' => 'debutant', 'reponses' => $rep])
            ->assertRedirect(route('tests.index'));
        $this->assertSame(0, TestNiveauResultat::count());
    }

    public function test_niveau_session_questions_are_single_use(): void
    {
        $this->seedParametres();
        $t = $this->niveauAvecQuestions(6);
        $this->postJson('/tests-de-niveau/questions', ['domaine' => 'logistique', 'niveau' => 'debutant']);
        $rep = $this->reponsesPour($t->questions()->with('reponses')->get());
        $this->post('/tests-de-niveau/soumettre', ['domaine' => 'logistique', 'niveau' => 'debutant', 'reponses' => $rep]);
        $this->post('/tests-de-niveau/soumettre', ['domaine' => 'logistique', 'niveau' => 'debutant', 'reponses' => $rep])
            ->assertRedirect(route('tests.index'));
        $this->assertSame(1, TestNiveauResultat::count());
    }

    public function test_niveau_wrong_answers_fail_and_score_computed(): void
    {
        $this->seedParametres();
        $t = $this->niveauAvecQuestions(4);
        $this->postJson('/tests-de-niveau/questions', ['domaine' => 'logistique', 'niveau' => 'debutant']);
        $rep = $this->reponsesPour($t->questions()->with('reponses')->get(), false);
        $this->post('/tests-de-niveau/soumettre', ['domaine' => 'logistique', 'niveau' => 'debutant', 'reponses' => $rep]);
        $row = TestNiveauResultat::first();
        $this->assertSame(0, $row->score);
        $this->assertFalse($row->passed);
    }

    public function test_niveau_success_awards_points_to_logged_in_user_once_per_day(): void
    {
        $this->seedParametres();
        $u = $this->apprenant();
        $t = $this->niveauAvecQuestions(4);
        for ($i = 0; $i < 2; $i++) {
            $this->actingAs($u)->postJson('/tests-de-niveau/questions', ['domaine' => 'logistique', 'niveau' => 'debutant']);
            $rep = $this->reponsesPour($t->questions()->with('reponses')->get());
            $this->actingAs($u)->post('/tests-de-niveau/soumettre', ['domaine' => 'logistique', 'niveau' => 'debutant', 'reponses' => $rep]);
        }
        $this->assertSame(2, $u->fresh()->points);
    }
}
