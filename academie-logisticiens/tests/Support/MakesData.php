<?php

namespace Tests\Support;

use App\Models\Certificat;
use App\Models\Challenge;
use App\Models\CodePromo;
use App\Models\Examen;
use App\Models\Formation;
use App\Models\Inscription;
use App\Models\Lecon;
use App\Models\Module;
use App\Models\Paiement;
use App\Models\Parametre;
use App\Models\Parcours;
use App\Models\Progression;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\Reponse;
use App\Models\TentativeQuiz;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * Fabriques de donnees reutilisables pour toute la suite de tests.
 */
trait MakesData
{
    private int $seqCounter = 0;

    protected function seq(): int
    {
        return ++$this->seqCounter;
    }

    // ───────── Utilisateurs ─────────

    protected function makeUser(string $role = 'apprenant', array $attrs = []): User
    {
        $n = $this->seq() . Str::lower(Str::random(5));

        $user = new User();
        $user->forceFill(array_merge([
            'name' => "Nom{$n}",
            'prenom' => "Prenom{$n}",
            'email' => "user{$n}@example.test",
            'password' => 'password', // hashe par le cast
            'role' => $role,
            'is_active' => true,
            'pays' => 'Congo',
            'date_naissance' => '1990-01-01',
            'lieu_naissance' => 'Brazzaville',
            'situation' => 'autre',
            'points' => 0,
            'jetons' => 0,
            'email_verified_at' => now(),
        ], $attrs))->save();

        return $user->fresh();
    }

    protected function apprenant(array $attrs = []): User
    {
        return $this->makeUser('apprenant', $attrs);
    }

    protected function admin(array $attrs = []): User
    {
        return $this->makeUser('admin', $attrs);
    }

    protected function gestionnaire(array $attrs = []): User
    {
        return $this->makeUser('gestionnaire', $attrs);
    }

    /** Charge utile d'inscription valide. */
    protected function registrationPayload(array $over = []): array
    {
        $n = $this->seq() . Str::lower(Str::random(4));

        return array_merge([
            'name' => 'Mabiala',
            'prenom' => 'Jean',
            'email' => "nouveau{$n}@example.test",
            'telephone' => '+242060000000',
            'password' => 'Str0ng-Passw0rd!x',
            'password_confirmation' => 'Str0ng-Passw0rd!x',
            'date_naissance' => '1995-05-20',
            'lieu_naissance' => 'Pointe-Noire',
            'situation' => 'autre',
            'pays' => 'Congo',
        ], $over);
    }

    /** Charge utile valide pour PATCH /profil. */
    protected function profilPayload(User $user, array $over = []): array
    {
        return array_merge([
            'name' => $user->name,
            'prenom' => $user->prenom ?? 'Prenom',
            'email' => $user->email,
            'date_naissance' => '1990-01-01',
            'lieu_naissance' => 'Brazzaville',
            'situation' => 'autre',
            'pays' => 'Congo',
        ], $over);
    }

    // ───────── Parametres ─────────

    /**
     * Vrai fichier uploade (contenu reel sniffe par finfo, contrairement a UploadedFile::fake()
     * qui devine le MIME d'apres le NOM du fichier).
     */
    protected function realUpload(string $name, string $content, ?string $mime = null): \Illuminate\Http\UploadedFile
    {
        $tmp = tempnam(sys_get_temp_dir(), 'aclt');
        file_put_contents($tmp, $content);

        return new \Illuminate\Http\UploadedFile($tmp, $name, $mime, null, true);
    }

    /** PNG 1x1 valide. */
    protected function pngBytes(): string
    {
        return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');
    }

    /** @return array<string, \Illuminate\Http\UploadedFile> fichiers dangereux deguises en images. */
    protected function disguisedFiles(string $ext = 'jpg'): array
    {
        return [
            'php-as-img' => $this->realUpload("shell.{$ext}", '<?php system($_GET["c"]); ?>'),
            'php-real-ext' => $this->realUpload('shell.php', '<?php echo 1;'),
            'html-as-img' => $this->realUpload("x.{$ext}", '<html><script>alert(1)</script></html>'),
            'svg-real-ext' => $this->realUpload('x.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'),
            'svg-as-img' => $this->realUpload("x.{$ext}", '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'),
            'js-as-img' => $this->realUpload("x.{$ext}", 'alert(document.cookie)'),
        ];
    }

    /** Remet a zero les compteurs de rate limiting (cache array) entre deux rafales de requetes. */
    protected function resetThrottle(): void
    {
        \Illuminate\Support\Facades\Cache::flush();
    }

    protected function seedParametres(array $over = []): void
    {
        $defaults = [
            'score_minimum_defaut' => '70',
            'score_minimum_quiz' => '70',
            'score_minimum_challenge' => '70',
            'points_test_reussi' => '2',
            'points_flag_capture' => '5',
            'seuil_jetons' => '100',
            'valeur_jetons_fcfa' => '5000',
        ];
        foreach (array_merge($defaults, $over) as $k => $v) {
            Parametre::definir($k, (string) $v);
        }
    }

    // ───────── Formations / contenu ─────────

    protected function formation(array $attrs = []): Formation
    {
        $n = $this->seq();

        return Formation::create(array_merge([
            'titre' => "Formation Test {$n}",
            'slug' => "formation-test-{$n}-" . Str::lower(Str::random(4)),
            'description' => 'Description de la formation',
            'programme' => "Programme\nLigne 2",
            'categorie' => 'logistique',
            'niveau' => 'debutant',
            'prix' => 50000,
            'duree_heures' => 10,
            'is_published' => true,
            'ordre' => $n,
        ], $attrs));
    }

    protected function unpublishedFormation(array $attrs = []): Formation
    {
        return $this->formation(array_merge(['is_published' => false], $attrs));
    }

    protected function module(Formation $formation, array $attrs = []): Module
    {
        $n = $this->seq();

        return Module::create(array_merge([
            'formation_id' => $formation->id,
            'titre' => "Module {$n}",
            'description' => 'desc',
            'ordre' => $n,
        ], $attrs));
    }

    protected function lecon(Module $module, array $attrs = []): Lecon
    {
        $n = $this->seq();

        return Lecon::create(array_merge([
            'module_id' => $module->id,
            'titre' => "Lecon {$n}",
            'contenu_texte' => 'Contenu de la lecon',
            'duree_minutes' => 5,
            'ordre' => $n,
        ], $attrs));
    }

    protected function quiz(Module $module, int $nbQuestions = 3): Quiz
    {
        $quiz = Quiz::create(['module_id' => $module->id, 'titre' => 'Quiz ' . $module->titre]);
        if ($nbQuestions > 0) {
            $this->addQuestions($quiz, $nbQuestions);
        }

        return $quiz;
    }

    /**
     * Ajoute $n questions (4 reponses, la 1ere est correcte) a un objet morph (Quiz, Examen, TestNiveau, Challenge).
     * Les textes de reponses sont neutres (ne revelent pas la bonne).
     *
     * @return \Illuminate\Support\Collection<int, Question>
     */
    protected function addQuestions($quizzable, int $n = 3)
    {
        $start = $quizzable->questions()->count();
        $out = collect();
        for ($i = 1; $i <= $n; $i++) {
            $q = $quizzable->questions()->create([
                'texte' => 'Question ' . ($start + $i) . ' ' . Str::random(4),
                'type' => 'qcm',
                'points' => 1,
                'ordre' => $start + $i,
            ]);
            foreach ([1, 2, 3, 4] as $k) {
                Reponse::create([
                    'question_id' => $q->id,
                    'texte' => "Choix {$k} de q{$q->id}",
                    'is_correct' => $k === 1,
                ]);
            }
            $out->push($q->load('reponses'));
        }

        return $out;
    }

    protected function examen(Formation $formation, string $type = 'final', array $attrs = [], int $nbQuestions = 5): Examen
    {
        $e = Examen::create(array_merge([
            'formation_id' => $formation->id,
            'type' => $type,
            'titre' => 'Examen ' . $type,
            'duree_minutes' => 60,
            'score_minimum' => 70,
            'nb_tentatives_max' => 1,
            'delai_entre_tentatives_heures' => 24,
            'nb_questions' => $nbQuestions,
        ], $attrs));
        if ($nbQuestions > 0) {
            $this->addQuestions($e, $nbQuestions);
        }

        return $e;
    }

    /**
     * Formation publiee complete : 2 modules x 2 lecons, un quiz (3 q) par module, examens test + final (5 q).
     */
    protected function formationComplete(array $attrs = []): array
    {
        $formation = $this->formation($attrs);
        $modules = collect();
        $lecons = collect();
        $quizzes = collect();
        for ($m = 1; $m <= 2; $m++) {
            $mod = $this->module($formation);
            $modules->push($mod);
            for ($l = 1; $l <= 2; $l++) {
                $lecons->push($this->lecon($mod));
            }
            $quizzes->push($this->quiz($mod, 3));
        }

        return [
            'formation' => $formation->fresh(),
            'modules' => $modules,
            'lecons' => $lecons,
            'quizzes' => $quizzes,
            'test' => $this->examen($formation, 'test', ['nb_tentatives_max' => 9999]),
            'final' => $this->examen($formation, 'final'),
        ];
    }

    protected function inscrire(User $user, Formation $formation, string $statut = 'active'): Inscription
    {
        return Inscription::updateOrCreate(
            ['user_id' => $user->id, 'formation_id' => $formation->id],
            ['statut' => $statut, 'date_acces' => now()]
        );
    }

    protected function completerLecons(User $user, Formation $formation): void
    {
        foreach ($formation->modules()->with('lecons')->get() as $m) {
            foreach ($m->lecons as $l) {
                Progression::updateOrCreate(
                    ['user_id' => $user->id, 'lecon_id' => $l->id],
                    ['is_completed' => true, 'completed_at' => now(), 'position_video' => 0]
                );
            }
        }
    }

    protected function reussirQuiz(User $user, Quiz $quiz, int $score = 100): TentativeQuiz
    {
        return TentativeQuiz::create([
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
            'score' => $score,
            'reponses_donnees' => [],
            'is_passed' => $score >= 70,
            'created_at' => now(),
        ]);
    }

    /** Debloque l'examen : toutes les lecons + tous les quiz reussis. */
    protected function debloquerExamens(User $user, array $fx): void
    {
        $this->completerLecons($user, $fx['formation']);
        foreach ($fx['quizzes'] as $quiz) {
            $this->reussirQuiz($user, $quiz);
        }
    }

    /** Reponses (question_id => reponse_id) correctes ou toutes fausses. */
    protected function reponsesPour($questions, bool $correct = true): array
    {
        $out = [];
        foreach ($questions as $q) {
            $q->loadMissing('reponses');
            $r = $correct
                ? $q->reponses->firstWhere('is_correct', true)
                : $q->reponses->firstWhere('is_correct', false);
            $out[$q->id] = $r->id;
        }

        return $out;
    }

    // ───────── CTF ─────────

    protected function parcours(array $attrs = []): Parcours
    {
        $n = $this->seq();

        return Parcours::create(array_merge([
            'titre' => "Parcours {$n}",
            'slug' => "parcours-{$n}-" . Str::lower(Str::random(4)),
            'description' => 'desc',
            'domaine' => 'logistique',
            'niveau' => 'debutant',
            'is_active' => true,
            'ordre' => $n,
        ], $attrs));
    }

    protected function challenge(Parcours $parcours, int $ordre = 1, int $nbQuestions = 10, array $attrs = []): Challenge
    {
        $c = Challenge::create(array_merge([
            'parcours_id' => $parcours->id,
            'titre' => "Etape {$ordre} " . Str::random(3),
            'theme' => 'theme',
            'description' => 'desc',
            'flag_attendu' => 'ACLOG{TEST_' . Str::upper(Str::random(5)) . '}',
            'points' => 5,
            'nb_questions' => 10,
            'ordre' => $ordre,
            'is_active' => true,
        ], $attrs));
        if ($nbQuestions > 0) {
            $this->addQuestions($c, $nbQuestions);
        }

        return $c;
    }

    // ───────── Commerce ─────────

    protected function paiement(User $user, Formation $formation, array $attrs = []): Paiement
    {
        return Paiement::create(array_merge([
            'user_id' => $user->id,
            'formation_id' => $formation->id,
            'montant' => $formation->prix_effectif,
            'capture_paiement' => null,
            'statut' => 'en_attente',
        ], $attrs));
    }

    protected function promo(array $attrs = []): CodePromo
    {
        $creator = $attrs['created_by'] ?? $this->admin()->id;
        unset($attrs['created_by']);

        return CodePromo::create(array_merge([
            'code' => 'PROMO' . Str::upper(Str::random(5)),
            'type' => 'pourcentage',
            'valeur' => 10,
            'perimetre' => 'all',
            'is_active' => true,
            'created_by' => $creator,
        ], $attrs));
    }

    protected function ticket(User $user, array $attrs = []): Ticket
    {
        return Ticket::create(array_merge([
            'user_id' => $user->id,
            'sujet' => 'Sujet de test',
            'message' => 'Message initial',
            'statut' => 'ouvert',
        ], $attrs));
    }

    protected function certificat(User $user, Formation $formation, array $attrs = []): Certificat
    {
        return Certificat::create(array_merge([
            'user_id' => $user->id,
            'formation_id' => $formation->id,
            'code_verification' => Str::upper(Str::random(8)) . '-' . Str::upper(Str::random(8)),
            'numero' => 'CFF-' . now()->year . '-ACLOG-CG-' . str_pad((string) $this->seq(), 3, '0', STR_PAD_LEFT) . 'CG',
            'score_obtenu' => 85,
            'issued_at' => now(),
            'is_public' => false,
        ], $attrs));
    }
}
