<?php

namespace Tests\Feature\Admin;

use App\Models\Examen;
use App\Models\Formation;
use App\Models\Lecon;
use App\Models\Module;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\Reponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CatalogueAdminTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->actingAs($this->admin());
    }

    private function formationPayload(array $over = []): array
    {
        return array_merge([
            'titre' => 'Nouvelle Formation Admin',
            'description' => 'Une description',
            'programme' => 'Programme',
            'categorie' => 'logistique',
            'niveau' => 'debutant',
            'prix' => 75000,
            'duree_heures' => 12,
            'is_published' => 1,
        ], $over);
    }

    public function test_dashboard_renders_with_stats(): void
    {
        $this->paiement($this->apprenant(), $this->formation(), ['statut' => 'valide', 'montant' => 1000]);
        $this->get('/admin/dashboard')->assertOk();
    }

    public function test_formation_create_page_and_store(): void
    {
        $this->get(route('admin.formations.create'))->assertOk();
        $res = $this->post(route('admin.formations.store'), $this->formationPayload());
        $f = Formation::where('titre', 'Nouvelle Formation Admin')->firstOrFail();
        $res->assertRedirect(route('admin.formations.contenu', $f));
        $this->assertSame('nouvelle-formation-admin', $f->slug);
        $this->assertTrue($f->is_published);
        $this->assertFalse($f->is_featured);
    }

    public function test_formation_store_validation(): void
    {
        $this->post(route('admin.formations.store'), [])->assertSessionHasErrors(['titre', 'description', 'categorie', 'niveau', 'prix', 'duree_heures']);
        $this->post(route('admin.formations.store'), $this->formationPayload(['niveau' => 'expert']))->assertSessionHasErrors('niveau');
        $this->post(route('admin.formations.store'), $this->formationPayload(['prix' => -1]))->assertSessionHasErrors('prix');
        $this->post(route('admin.formations.store'), $this->formationPayload(['duree_heures' => 0]))->assertSessionHasErrors('duree_heures');
    }

    public function test_formation_store_with_image_upload(): void
    {
        $this->post(route('admin.formations.store'), $this->formationPayload(['image' => UploadedFile::fake()->image('cover.jpg', 300, 200)]))->assertSessionHasNoErrors();
        $f = Formation::first();
        $this->assertNotNull($f->image);
        Storage::disk('public')->assertExists($f->image);
    }

    public function test_formation_image_rejects_dangerous_files(): void
    {
        foreach ($this->disguisedFiles('jpg') as $k => $file) {
            $this->post(route('admin.formations.store'), $this->formationPayload(['image' => $file]))->assertSessionHasErrors('image');
        }
        $this->assertSame(0, Formation::count());
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_formation_with_duplicate_title_does_not_crash(): void
    {
        // FINDING : slug = Str::slug(titre) sans unicite -> QueryException 500 sur titre en double.
        $this->post(route('admin.formations.store'), $this->formationPayload());
        $res = $this->post(route('admin.formations.store'), $this->formationPayload());
        $this->assertLessThan(500, $res->status(), 'Titre en double => erreur SQL 500 (slug unique).');
    }

    public function test_formation_edit_update_and_delete(): void
    {
        $f = $this->formation(['titre' => 'Avant']);
        $this->get(route('admin.formations.edit', $f))->assertOk()->assertSee('Avant');
        $this->put(route('admin.formations.update', $f), $this->formationPayload(['titre' => 'Apres', 'is_published' => 0, 'is_featured' => 1]))
            ->assertRedirect(route('admin.formations.index'));
        $f->refresh();
        $this->assertSame('Apres', $f->titre);
        $this->assertFalse($f->is_published);
        $this->assertTrue($f->is_featured);

        $this->delete(route('admin.formations.destroy', $f))->assertRedirect();
        $this->assertNull(Formation::find($f->id));
    }

    public function test_formation_index_lists_everything_including_unpublished(): void
    {
        $this->unpublishedFormation(['titre' => 'Brouillon Admin Visible']);
        $this->get(route('admin.formations.index'))->assertOk()->assertSee('Brouillon Admin Visible');
    }

    public function test_formation_titles_are_escaped_in_admin_list(): void
    {
        $this->formation(['titre' => '<script>alert(1)</script>']);
        $this->get(route('admin.formations.index'))->assertOk()->assertDontSee('<script>alert(1)</script>', false);
    }

    // ───────── Contenu ─────────

    public function test_content_page_and_module_crud(): void
    {
        $f = $this->formation();
        $this->get(route('admin.formations.contenu', $f))->assertOk();

        $this->post(route('admin.modules.store', $f), ['titre' => 'Mod 1', 'description' => 'd'])->assertRedirect();
        $this->post(route('admin.modules.store', $f), ['titre' => 'Mod 2'])->assertRedirect();
        $this->assertSame([1, 2], $f->modules()->pluck('ordre')->all());
        $this->post(route('admin.modules.store', $f), [])->assertSessionHasErrors('titre');

        $m = $f->modules()->first();
        $this->put(route('admin.modules.update', $m), ['titre' => 'Renomme'])->assertRedirect();
        $this->assertSame('Renomme', $m->fresh()->titre);
        $this->delete(route('admin.modules.destroy', $m))->assertRedirect();
        $this->assertNull(Module::find($m->id));
    }

    public function test_lesson_crud_and_validation(): void
    {
        $m = $this->module($this->formation());
        $this->post(route('admin.lecons.store', $m), ['titre' => 'L1', 'contenu_texte' => 'abc', 'video_url' => 'https://youtu.be/xyz', 'duree_minutes' => 8, 'is_preview' => 1])->assertSessionHasNoErrors();
        $l = Lecon::first();
        $this->assertTrue($l->is_preview);
        $this->assertSame(1, $l->ordre);

        $this->post(route('admin.lecons.store', $m), ['titre' => ''])->assertSessionHasErrors('titre');
        $this->post(route('admin.lecons.store', $m), ['titre' => 'X', 'video_url' => 'pas-une-url'])->assertSessionHasErrors('video_url');
        $this->post(route('admin.lecons.store', $m), ['titre' => 'X', 'duree_minutes' => 0])->assertSessionHasErrors('duree_minutes');

        $this->put(route('admin.lecons.update', $l), ['titre' => 'L1 bis'])->assertRedirect();
        $this->assertSame('L1 bis', $l->fresh()->titre);
        $this->assertFalse($l->fresh()->is_preview);
        $this->delete(route('admin.lecons.destroy', $l))->assertRedirect();
        $this->assertSame(0, Lecon::count());
    }

    public function test_lesson_video_url_rejects_javascript_scheme(): void
    {
        $m = $this->module($this->formation());
        $this->post(route('admin.lecons.store', $m), ['titre' => 'X', 'video_url' => 'javascript:alert(1)'])->assertSessionHasErrors('video_url');
    }

    public function test_quiz_and_questions_crud_with_cascade_delete(): void
    {
        $m = $this->module($this->formation());
        $this->post(route('admin.quiz.store', $m), ['titre' => 'Mon quiz'])->assertSessionHas('success');
        $this->post(route('admin.quiz.store', $m))->assertSessionHas('error'); // deja un quiz
        $quiz = Quiz::first();

        $this->post(route('admin.questions.store', $quiz), [
            'texte' => 'Capitale du Congo ?', 'type' => 'qcm',
            'reponses' => [['texte' => 'Brazzaville', 'is_correct' => 1], ['texte' => 'Pointe-Noire']],
        ])->assertSessionHasNoErrors();
        $q = Question::first();
        $this->assertSame(2, $q->reponses()->count());
        $this->assertSame(['Brazzaville'], $q->reponses()->where('is_correct', true)->pluck('texte')->all());

        $this->post(route('admin.questions.store', $quiz), ['texte' => 'x', 'type' => 'qcm', 'reponses' => [['texte' => 'seule']]])->assertSessionHasErrors('reponses');
        $this->post(route('admin.questions.store', $quiz), ['texte' => 'x', 'type' => 'bizarre', 'reponses' => [['texte' => 'a'], ['texte' => 'b']]])->assertSessionHasErrors('type');

        $this->delete(route('admin.questions.destroy', $q))->assertRedirect();
        $this->assertSame(0, Question::count());
        $this->assertSame(0, Reponse::count());
    }

    public function test_deleting_module_removes_quiz_questions_and_answers(): void
    {
        $f = $this->formation();
        $m = $this->module($f);
        $this->lecon($m);
        $this->quiz($m, 3);
        $this->assertSame(3, Question::count());
        $this->delete(route('admin.modules.destroy', $m))->assertRedirect();
        $this->assertSame(0, Quiz::count());
        $this->assertSame(0, Question::count(), 'Questions morph orphelines apres suppression du module');
        $this->assertSame(0, Reponse::count());
    }

    public function test_deleting_formation_removes_exams_and_their_questions(): void
    {
        $fx = $this->formationComplete();
        $this->assertGreaterThan(0, Question::count());
        $this->delete(route('admin.formations.destroy', $fx['formation']))->assertRedirect();
        $this->assertSame(0, Examen::count());
        $this->assertSame(0, Question::count(), 'Questions morph orphelines apres suppression de la formation');
    }

    public function test_question_needs_at_least_one_correct_answer(): void
    {
        // FINDING : une question sans bonne reponse est acceptee -> question impossible a reussir.
        $quiz = $this->quiz($this->module($this->formation()), 0);
        $res = $this->post(route('admin.questions.store', $quiz), [
            'texte' => 'Sans bonne reponse', 'type' => 'qcm', 'reponses' => [['texte' => 'a'], ['texte' => 'b']],
        ]);
        $res->assertSessionHasErrors();
    }

    public function test_question_text_is_escaped_in_admin_content_page(): void
    {
        $f = $this->formation();
        $m = $this->module($f);
        $quiz = $this->quiz($m, 0);
        $this->post(route('admin.questions.store', $quiz), [
            'texte' => '<img src=x onerror=alert(1)>', 'type' => 'qcm',
            'reponses' => [['texte' => '<script>1</script>', 'is_correct' => 1], ['texte' => 'b']],
        ]);
        $this->get(route('admin.formations.contenu', $f))->assertOk()
            ->assertDontSee('<img src=x onerror=alert(1)>', false)->assertDontSee('<script>1</script>', false);
    }

    // ───────── Examens ─────────

    public function test_exam_store_or_update_creates_test_and_final_and_updates_in_place(): void
    {
        $f = $this->formation();
        $this->get(route('admin.formations.examens', $f))->assertOk();
        $payload = ['type' => 'final', 'titre' => 'Final', 'duree_minutes' => 90, 'score_minimum' => 75, 'nb_questions' => 20, 'nb_tentatives_max' => 2];
        $this->post(route('admin.formations.examens.store', $f), $payload)->assertSessionHasNoErrors();
        $this->post(route('admin.formations.examens.store', $f), array_merge($payload, ['type' => 'test', 'titre' => 'Test']))->assertSessionHasNoErrors();
        $this->assertSame(2, Examen::count());

        $this->post(route('admin.formations.examens.store', $f), array_merge($payload, ['score_minimum' => 80]));
        $this->assertSame(2, Examen::count());
        $this->assertSame(80, $f->examenFinal()->first()->score_minimum);
    }

    public function test_exam_validation(): void
    {
        $f = $this->formation();
        $base = ['type' => 'final', 'titre' => 'F', 'duree_minutes' => 60, 'score_minimum' => 70, 'nb_questions' => 5, 'nb_tentatives_max' => 1];
        $this->post(route('admin.formations.examens.store', $f), array_merge($base, ['type' => 'autre']))->assertSessionHasErrors('type');
        $this->post(route('admin.formations.examens.store', $f), array_merge($base, ['duree_minutes' => 1]))->assertSessionHasErrors('duree_minutes');
        $this->post(route('admin.formations.examens.store', $f), array_merge($base, ['score_minimum' => 101]))->assertSessionHasErrors('score_minimum');
        $this->post(route('admin.formations.examens.store', $f), array_merge($base, ['score_minimum' => 0]))->assertSessionHasErrors('score_minimum');
        $this->post(route('admin.formations.examens.store', $f), array_merge($base, ['nb_questions' => 0]))->assertSessionHasErrors('nb_questions');
        $this->post(route('admin.formations.examens.store', $f), array_merge($base, ['nb_tentatives_max' => 0]))->assertSessionHasErrors('nb_tentatives_max');
    }

    public function test_exam_question_add_and_delete(): void
    {
        $f = $this->formation();
        $exam = $this->examen($f, 'final', [], 0);
        $this->post(route('admin.examens.questions.store', $exam), [
            'texte' => 'Q ?', 'type' => 'vrai_faux', 'points' => 2,
            'reponses' => [['texte' => 'Vrai', 'is_correct' => 1], ['texte' => 'Faux']],
        ])->assertSessionHasNoErrors();
        $q = $exam->questions()->first();
        $this->assertSame(2, $q->points);
        $this->assertSame('vrai_faux', $q->type);

        $this->delete(route('admin.examens.questions.destroy', $q))->assertRedirect();
        $this->assertSame(0, Question::count());
        $this->assertSame(0, Reponse::count());
    }

    public function test_exam_page_lists_failed_attempts_for_reactivation(): void
    {
        $fx = $this->formationComplete();
        $learner = $this->apprenant(['name' => 'ApprenantRate']);
        \App\Models\TentativeExamen::create([
            'user_id' => $learner->id, 'examen_id' => $fx['final']->id, 'score' => 20, 'reponses_donnees' => [],
            'is_passed' => false, 'numero_tentative' => 1, 'is_active' => true, 'created_at' => now(),
        ]);
        $this->get(route('admin.formations.examens', $fx['formation']))->assertOk()->assertSee('ApprenantRate');
    }
}
