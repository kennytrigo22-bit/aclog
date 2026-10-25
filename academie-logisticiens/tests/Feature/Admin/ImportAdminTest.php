<?php

namespace Tests\Feature\Admin;

use App\Models\Examen;
use App\Models\Module;
use App\Models\Question;
use App\Models\Reponse;
use App\Models\TestNiveau;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ImportAdminTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->admin());
    }

    private function qcmTexte(int $n = 3): string
    {
        $blocks = [];
        for ($i = 1; $i <= $n; $i++) {
            $blocks[] = "{$i}. Question numero {$i} ?\nA. Mauvaise 1\nB. Bonne reponse {$i} *\nC. Mauvaise 2\nD. Mauvaise 3";
        }

        return implode("\n\n", $blocks);
    }

    private function fakeGemini(array $payload): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/upload/v1beta/files*' => Http::response('', 200, ['X-Goog-Upload-URL' => 'https://generativelanguage.googleapis.com/upload-session/abc']),
            'generativelanguage.googleapis.com/upload-session/*' => Http::response(['file' => ['uri' => 'https://generativelanguage.googleapis.com/v1beta/files/abc', 'name' => 'files/abc', 'state' => 'ACTIVE']]),
            'generativelanguage.googleapis.com/v1beta/models/*' => Http::response(['candidates' => [['content' => ['parts' => [['text' => json_encode($payload)]]]]]]),
            'generativelanguage.googleapis.com/v1beta/files/*' => Http::response([], 200),
            '*' => Http::response('unexpected host', 500),
        ]);
    }

    private function pdf(string $name = 'cours.pdf')
    {
        return $this->realUpload($name, "%PDF-1.4\n1 0 obj<</Type/Catalog>>endobj\ntrailer<</Root 1 0 R>>\n%%EOF", 'application/pdf');
    }

    // ───────── QCM texte ─────────

    public function test_index_lists_targets_and_ai_availability(): void
    {
        $fx = $this->formationComplete(['titre' => 'Formation Cible']);
        $this->get(route('admin.qcm-import.index'))->assertOk()->assertSee('Formation Cible')
            ->assertSee('exam:' . $fx['formation']->id . ':final')->assertSee('quiz:' . $fx['modules'][0]->id);
    }

    public function test_analyze_preview_and_save_into_final_exam_add_mode(): void
    {
        $f = $this->formation();
        $cible = "exam:{$f->id}:final";
        $this->post(route('admin.qcm-import.analyze'), ['cible' => $cible, 'mode' => 'ajouter', 'texte' => $this->qcmTexte(3)])->assertRedirect(route('admin.qcm-import.apercu'));
        $this->get(route('admin.qcm-import.apercu'))->assertOk()->assertSee('Question numero 1');

        $this->post(route('admin.qcm-import.save'), ['keep' => [0, 1, 2]])->assertRedirect()->assertSessionHas('success');
        $exam = Examen::where('formation_id', $f->id)->where('type', 'final')->firstOrFail();
        $this->assertSame(3, $exam->questions()->count());
        $this->assertSame(1, Reponse::where('question_id', $exam->questions()->first()->id)->where('is_correct', true)->count());
        $this->assertSame('Bonne reponse 1', Reponse::where('question_id', $exam->questions()->first()->id)->where('is_correct', true)->first()->texte);
        $this->assertSame(1, $exam->questions()->first()->ordre);
    }

    public function test_add_mode_appends_and_replace_mode_replaces(): void
    {
        $f = $this->formation();
        $exam = $this->examen($f, 'test', [], 4);
        $cible = "exam:{$f->id}:test";

        $this->post(route('admin.qcm-import.analyze'), ['cible' => $cible, 'mode' => 'ajouter', 'texte' => $this->qcmTexte(2)]);
        $this->post(route('admin.qcm-import.save'), ['keep' => [0, 1]]);
        $this->assertSame(6, $exam->questions()->count());

        $this->post(route('admin.qcm-import.analyze'), ['cible' => $cible, 'mode' => 'remplacer', 'texte' => $this->qcmTexte(2)]);
        $this->post(route('admin.qcm-import.save'), ['keep' => [0, 1]]);
        $this->assertSame(2, $exam->questions()->count());
        $this->assertSame(2, Question::count(), 'Les anciennes questions doivent etre supprimees');
        $this->assertSame(8, Reponse::count());
    }

    public function test_save_only_keeps_selected_questions(): void
    {
        $f = $this->formation();
        $this->post(route('admin.qcm-import.analyze'), ['cible' => "exam:{$f->id}:final", 'mode' => 'ajouter', 'texte' => $this->qcmTexte(4)]);
        $this->post(route('admin.qcm-import.save'), ['keep' => [1, 3]]);
        $this->assertSame(2, Question::count());
        $this->assertSame(['Question numero 2 ?', 'Question numero 4 ?'], Question::orderBy('id')->pluck('texte')->all());
        $this->post(route('admin.qcm-import.save'), ['keep' => [1]])->assertRedirect(route('admin.qcm-import.index')); // session consommee
    }

    public function test_save_with_no_selection_errors_and_session_expiry_redirects(): void
    {
        $this->post(route('admin.qcm-import.save'), ['keep' => [0]])->assertRedirect(route('admin.qcm-import.index'))->assertSessionHas('error');
        $f = $this->formation();
        $this->post(route('admin.qcm-import.analyze'), ['cible' => "exam:{$f->id}:final", 'mode' => 'ajouter', 'texte' => $this->qcmTexte(2)]);
        $this->post(route('admin.qcm-import.save'), ['keep' => []])->assertSessionHas('error');
        $this->assertSame(0, Question::count());
        $this->get(route('admin.qcm-import.apercu'))->assertOk();
    }

    public function test_apercu_without_session_redirects(): void
    {
        $this->get(route('admin.qcm-import.apercu'))->assertRedirect(route('admin.qcm-import.index'));
    }

    public function test_import_into_module_quiz_creates_quiz(): void
    {
        $f = $this->formation();
        $m = $this->module($f);
        $this->post(route('admin.qcm-import.analyze'), ['cible' => "quiz:{$m->id}", 'mode' => 'ajouter', 'texte' => $this->qcmTexte(3)]);
        $this->post(route('admin.qcm-import.save'), ['keep' => [0, 1, 2]]);
        $this->assertNotNull($m->fresh()->quiz);
        $this->assertSame(3, $m->fresh()->quiz->questions()->count());
    }

    public function test_import_into_test_niveau(): void
    {
        $t = TestNiveau::create(['domaine' => 'douane', 'niveau' => 'debutant', 'titre' => 'T', 'nb_questions' => 10, 'is_active' => true]);
        $this->post(route('admin.qcm-import.analyze'), ['cible' => "niveau:{$t->id}", 'mode' => 'ajouter', 'texte' => $this->qcmTexte(2)]);
        $this->post(route('admin.qcm-import.save'), ['keep' => [0, 1]]);
        $this->assertSame(2, $t->questions()->count());
    }

    public function test_import_into_ctf_step_and_minimum_shown_in_preview(): void
    {
        $c = $this->challenge($this->parcours(), 1, 0);
        $this->post(route('admin.qcm-import.analyze'), ['cible' => "etape:{$c->id}", 'mode' => 'ajouter', 'texte' => $this->qcmTexte(10)]);
        $this->get(route('admin.qcm-import.apercu'))->assertOk();
        $this->post(route('admin.qcm-import.save'), ['keep' => range(0, 9)]);
        $this->assertSame(10, $c->questions()->count());
        $this->assertTrue($c->fresh()->isJouable());
    }

    public function test_analyze_validation_and_invalid_targets(): void
    {
        $f = $this->formation();
        $this->post(route('admin.qcm-import.analyze'), [])->assertSessionHasErrors(['cible', 'mode', 'texte']);
        $this->post(route('admin.qcm-import.analyze'), ['cible' => "exam:{$f->id}:final", 'mode' => 'detruire', 'texte' => 'x'])->assertSessionHasErrors('mode');
        $this->post(route('admin.qcm-import.analyze'), ['cible' => "exam:{$f->id}:hack", 'mode' => 'ajouter', 'texte' => $this->qcmTexte(1)])->assertNotFound();
        $this->post(route('admin.qcm-import.analyze'), ['cible' => 'exam:99999:final', 'mode' => 'ajouter', 'texte' => $this->qcmTexte(1)])->assertNotFound();
        $this->post(route('admin.qcm-import.analyze'), ['cible' => 'inconnu:1', 'mode' => 'ajouter', 'texte' => $this->qcmTexte(1)])->assertNotFound();
        $this->post(route('admin.qcm-import.analyze'), ['cible' => "exam:{$f->id}:final", 'mode' => 'ajouter', 'texte' => "juste une ligne"])->assertSessionHas('error');
    }

    public function test_parser_defaults_first_answer_correct_and_strips_prefixes(): void
    {
        $f = $this->formation();
        $this->post(route('admin.qcm-import.analyze'), ['cible' => "exam:{$f->id}:final", 'mode' => 'ajouter', 'texte' => "1) Sans etoile ?\na) Alpha\nb) Beta"]);
        $this->post(route('admin.qcm-import.save'), ['keep' => [0]]);
        $q = Question::first();
        $this->assertSame('Sans etoile ?', $q->texte);
        $this->assertSame(['Alpha'], $q->reponses()->where('is_correct', true)->pluck('texte')->all());
    }

    public function test_html_in_imported_text_is_stored_raw_but_rendered_escaped_in_admin_preview(): void
    {
        $f = $this->formation();
        $this->post(route('admin.qcm-import.analyze'), ['cible' => "exam:{$f->id}:final", 'mode' => 'ajouter', 'texte' => "<script>alert(1)</script> ?\nA. <b>x</b> *\nB. y"]);
        $this->get(route('admin.qcm-import.apercu'))->assertOk()->assertDontSee('<script>alert(1)</script>', false);
    }

    // ───────── QCM par IA (Gemini) ─────────

    public function test_ai_path_lancer_lot_terminer_then_save(): void
    {
        $questions = [];
        for ($i = 1; $i <= 12; $i++) {
            $questions[] = ['texte' => "Question IA {$i}", 'type' => 'qcm', 'reponses' => [
                ['texte' => 'Juste', 'is_correct' => true], ['texte' => 'Faux 1', 'is_correct' => false], ['texte' => 'Faux 2', 'is_correct' => false],
            ]];
        }
        $questions[] = ['texte' => 'Sans bonne reponse', 'type' => 'qcm', 'reponses' => [['texte' => 'a', 'is_correct' => false], ['texte' => 'b', 'is_correct' => false]]];
        $questions[] = ['texte' => 'Question IA 1', 'type' => 'qcm', 'reponses' => [['texte' => 'x', 'is_correct' => true], ['texte' => 'y', 'is_correct' => false]]]; // doublon
        $this->fakeGemini(['questions' => $questions]);

        $f = $this->formation();
        $cible = "exam:{$f->id}:final";

        $this->postJson(route('admin.qcm-import.lancer'), ['cible' => $cible, 'mode' => 'ajouter', 'documents' => [$this->pdf()]])->assertOk()->assertJson(['ok' => true]);
        $this->assertNotNull(session('qcm_ai'));

        $lot = $this->postJson(route('admin.qcm-import.lot'))->assertOk()->json();
        $this->assertSame(13, $lot['total']);       // question sans bonne reponse ecartee (le doublon est traite a "terminer")
        $this->assertFalse($lot['fini']);           // 14 questions brutes >= 10 -> d'autres lots possibles
        $this->postJson(route('admin.qcm-import.terminer'))->assertOk();
        $this->assertCount(12, session('qcm_import.questions'));
        $this->post(route('admin.qcm-import.save'), ['keep' => range(0, 11)])->assertSessionHas('success');
        $this->assertSame(12, Examen::where('formation_id', $f->id)->where('type', 'final')->first()->questions()->count());
    }

    public function test_ai_terminer_dedupes_and_prepares_preview(): void
    {
        $this->fakeGemini(['questions' => [
            ['texte' => 'Dup', 'type' => 'qcm', 'reponses' => [['texte' => 'a', 'is_correct' => true], ['texte' => 'b', 'is_correct' => false]]],
            ['texte' => '  dup ', 'type' => 'qcm', 'reponses' => [['texte' => 'a', 'is_correct' => true], ['texte' => 'b', 'is_correct' => false]]],
            ['texte' => 'Autre', 'type' => 'vrai_faux', 'reponses' => [['texte' => 'Vrai', 'is_correct' => true], ['texte' => 'Faux', 'is_correct' => false]]],
        ]]);
        $f = $this->formation();
        $this->postJson(route('admin.qcm-import.lancer'), ['cible' => "exam:{$f->id}:test", 'mode' => 'remplacer', 'documents' => [$this->pdf()]])->assertOk();
        $this->postJson(route('admin.qcm-import.lot'))->assertOk();
        $res = $this->postJson(route('admin.qcm-import.terminer'))->assertOk()->json();
        $this->assertSame(route('admin.qcm-import.apercu'), $res['url']);
        $this->assertCount(2, session('qcm_import.questions'));
        $this->assertSame('remplacer', session('qcm_import.mode'));
        $this->assertNull(session('qcm_ai'));

        $this->post(route('admin.qcm-import.save'), ['keep' => [0, 1]])->assertSessionHas('success');
        $this->assertSame(2, Examen::where('formation_id', $f->id)->where('type', 'test')->first()->questions()->count());
        Http::assertSent(fn (Request $r) => str_contains($r->url(), 'generateContent'));
    }

    public function test_ai_lot_and_terminer_without_state_fail_cleanly(): void
    {
        $this->postJson(route('admin.qcm-import.lot'))->assertStatus(422);
        $this->postJson(route('admin.qcm-import.terminer'))->assertStatus(422);
    }

    public function test_ai_lancer_validation_and_missing_key(): void
    {
        $f = $this->formation();
        $this->postJson(route('admin.qcm-import.lancer'), [])->assertStatus(422);
        $this->postJson(route('admin.qcm-import.lancer'), ['cible' => "exam:{$f->id}:final", 'mode' => 'ajouter', 'documents' => [$this->realUpload('virus.exe', 'MZ')]])->assertStatus(422);

        config(['services.gemini.api_key' => null]);
        Http::fake();
        $this->postJson(route('admin.qcm-import.lancer'), ['cible' => "exam:{$f->id}:final", 'mode' => 'ajouter', 'documents' => [$this->pdf()]])->assertStatus(422);
        Http::assertNothingSent();
    }

    public function test_ai_api_error_is_reported_without_500(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/upload/v1beta/files*' => Http::response('', 500),
            '*' => Http::response('nope', 500),
        ]);
        $f = $this->formation();
        $this->postJson(route('admin.qcm-import.lancer'), ['cible' => "exam:{$f->id}:final", 'mode' => 'ajouter', 'documents' => [$this->pdf()]])->assertStatus(422);
    }

    public function test_gemini_never_sends_the_api_key_to_a_foreign_host(): void
    {
        $this->fakeGemini(['questions' => []]);
        $f = $this->formation();
        $this->postJson(route('admin.qcm-import.lancer'), ['cible' => "exam:{$f->id}:final", 'mode' => 'ajouter', 'documents' => [$this->pdf()]]);
        Http::assertSent(fn (Request $r) => str_starts_with($r->url(), 'https://generativelanguage.googleapis.com/'));
        Http::assertNotSent(fn (Request $r) => !str_contains($r->url(), 'generativelanguage.googleapis.com'));
    }

    // ───────── Import IA de formation ─────────

    private function geminiFormationJson(): array
    {
        $q = fn ($t) => ['texte' => $t, 'type' => 'qcm', 'reponses' => [['texte' => 'Juste', 'is_correct' => true], ['texte' => 'Faux', 'is_correct' => false]]];

        return [
            'modules' => [[
                'titre' => 'Module IA 1', 'description' => 'desc',
                'lecons' => [['titre' => 'Lecon IA 1', 'contenu_texte' => 'Texte', 'duree_minutes' => 10], ['titre' => 'Lecon IA 2', 'contenu_texte' => 'Texte 2', 'duree_minutes' => 5]],
                'quiz' => ['questions' => [$q('Quiz IA q1'), $q('Quiz IA q2')]],
            ]],
            'examen_test' => ['questions' => [$q('Test IA q1')]],
            'examen_final' => ['questions' => [$q('Final IA q1'), $q('Final IA q2')]],
        ];
    }

    public function test_gemini_upload_page_and_analyze_shows_preview(): void
    {
        $this->fakeGemini($this->geminiFormationJson());
        $f = $this->formation();
        $this->get(route('admin.formations.gemini.upload', $f))->assertOk();
        $res = $this->post(route('admin.formations.gemini.analyze', $f), [
            'mode_contenu' => 'ajouter', 'mode_examens' => 'remplacer', 'documents' => [$this->pdf()],
        ])->assertOk()->assertSee('Module IA 1');
        $this->assertSame('remplacer', session("gemini_modes_{$f->id}")['examens']);
        Http::assertSent(fn (Request $r) => str_contains($r->url(), 'generateContent') && $r->hasHeader('x-goog-api-key', 'test-gemini-key'));
        // la cle ne doit plus transiter dans l'URL (logs, historique, proxies)
        Http::assertNotSent(fn (Request $r) => str_contains($r->url(), 'key=') || str_contains($r->url(), 'test-gemini-key'));
    }

    public function test_gemini_analyze_validation(): void
    {
        $f = $this->formation();
        $this->post(route('admin.formations.gemini.analyze', $f), [])->assertSessionHasErrors(['mode_contenu', 'mode_examens', 'documents']);
        $this->post(route('admin.formations.gemini.analyze', $f), ['mode_contenu' => 'ajouter', 'mode_examens' => 'ajouter', 'documents' => [$this->realUpload('x.php', '<?php echo 1;')]])->assertSessionHasErrors('documents.0');
        $this->post(route('admin.formations.gemini.analyze', $f), ['mode_contenu' => 'x', 'mode_examens' => 'ajouter', 'documents' => [$this->pdf()]])->assertSessionHasErrors('mode_contenu');
    }

    public function test_gemini_analyze_without_key_or_with_api_error_returns_message(): void
    {
        $f = $this->formation();
        config(['services.gemini.api_key' => '']);
        Http::fake();
        $this->post(route('admin.formations.gemini.analyze', $f), ['mode_contenu' => 'ajouter', 'mode_examens' => 'ajouter', 'documents' => [$this->pdf()]])->assertSessionHas('error');
        Http::assertNothingSent();

        config(['services.gemini.api_key' => 'k']);
        Http::fake(['*' => Http::response('boom', 500)]);
        $this->post(route('admin.formations.gemini.analyze', $f), ['mode_contenu' => 'ajouter', 'mode_examens' => 'ajouter', 'documents' => [$this->pdf()]])->assertSessionHas('error');
    }

    private function savePayload(): array
    {
        $d = $this->geminiFormationJson();
        // le formulaire d'apercu poste is_correct sous forme de "1"
        foreach ($d['modules'][0]['quiz']['questions'] as &$q) {
            $q['reponses'][0]['is_correct'] = '1';
            unset($q['reponses'][1]['is_correct']);
        }
        foreach (['examen_test', 'examen_final'] as $k) {
            foreach ($d[$k]['questions'] as &$q) {
                $q['reponses'][0]['is_correct'] = '1';
                unset($q['reponses'][1]['is_correct']);
            }
        }

        return $d;
    }

    public function test_gemini_save_add_mode_creates_content_and_exams(): void
    {
        $f = $this->formation();
        $this->module($f, ['titre' => 'Module existant']);
        $this->post(route('admin.formations.gemini.save', $f), $this->savePayload())->assertRedirect(route('admin.formations.contenu', $f))->assertSessionHas('success');

        $this->assertSame(2, $f->modules()->count());
        $mod = Module::where('titre', 'Module IA 1')->first();
        $this->assertSame(2, $mod->lecons()->count());
        $this->assertSame(2, $mod->quiz->questions()->count());
        $this->assertSame(1, Examen::where('formation_id', $f->id)->where('type', 'test')->first()->questions()->count());
        $this->assertSame(2, Examen::where('formation_id', $f->id)->where('type', 'final')->first()->questions()->count());
        $this->assertSame(1, Reponse::where('question_id', $mod->quiz->questions()->first()->id)->where('is_correct', true)->count());
    }

    public function test_gemini_save_replace_mode_replaces_content_and_exam_questions(): void
    {
        $fx = $this->formationComplete();
        $f = $fx['formation'];
        $this->withSession(["gemini_modes_{$f->id}" => ['contenu' => 'remplacer', 'examens' => 'remplacer']])
            ->post(route('admin.formations.gemini.save', $f), $this->savePayload())->assertSessionHas('success');

        $this->assertSame(['Module IA 1'], $f->modules()->pluck('titre')->all());
        $this->assertSame(2, Examen::find($fx['final']->id)->questions()->count());
        $this->assertSame(1, Examen::find($fx['test']->id)->questions()->count());
        // anciennes questions du quiz supprimees avec leurs modules
        $this->assertSame(2 + 1 + 2, Question::count());
    }

    public function test_gemini_save_validation(): void
    {
        $f = $this->formation();
        $this->post(route('admin.formations.gemini.save', $f), [])->assertSessionHasErrors('modules');
        $this->post(route('admin.formations.gemini.save', $f), ['modules' => [['titre' => '']]])->assertSessionHasErrors('modules.0.titre');
        $bad = $this->savePayload();
        $bad['modules'][0]['quiz']['questions'][0]['type'] = 'dissertation';
        $this->post(route('admin.formations.gemini.save', $f), $bad)->assertSessionHasErrors('modules.0.quiz.questions.0.type');
        $this->assertSame(0, $f->modules()->count());
    }
}
