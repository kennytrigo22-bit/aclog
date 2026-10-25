<?php

namespace App\Console\Commands;

use App\Models\Examen;
use App\Models\Formation;
use App\Models\Question;
use App\Models\Reponse;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImporterFormation extends Command
{
    protected $signature = 'formation:importer {fichier : JSON genere depuis les documents Word}';

    protected $description = 'Cree ou remplace une formation (modules, lecons, quiz, examens test et final) a partir d\'un fichier JSON.';

    public function handle(): int
    {
        $data = json_decode(file_get_contents($this->argument('fichier')), true);
        if (!$data) {
            $this->error('Fichier JSON invalide.');
            return self::FAILURE;
        }

        DB::transaction(function () use ($data) {
            $formation = Formation::updateOrCreate(['slug' => $data['slug']], [
                'titre' => $data['titre'],
                'description' => $data['description'] ?: $data['titre'],
                'programme' => $data['programme'] ?? null,
                'categorie' => $data['categorie'],
                'niveau' => $data['niveau'],
                'prix' => $data['prix'],
                'duree_heures' => $data['duree_heures'],
                'is_published' => true,
                'badge' => 'nouveau',
                'ordre' => (int) Formation::max('ordre') + 1,
            ]);

            foreach ($formation->modules()->with('quiz')->get() as $ancien) {
                $ancien->quiz?->delete();
                $ancien->delete();
            }

            foreach ($data['modules'] as $mi => $m) {
                $module = $formation->modules()->create([
                    'titre' => $m['titre'], 'description' => $m['description'] ?: null, 'ordre' => $mi + 1,
                ]);

                foreach ($m['lecons'] as $li => $l) {
                    $module->lecons()->create([
                        'titre' => $l['titre'],
                        'contenu_texte' => $l['html'],
                        'video_url' => $l['video_url'],
                        'duree_minutes' => $l['duree_minutes'],
                        'ordre' => $li + 1,
                    ]);
                }

                if (!empty($m['quiz'])) {
                    $quiz = $module->quiz()->create(['titre' => 'Quiz - ' . $m['titre']]);
                    $this->questions($quiz, $m['quiz']);
                }
            }

            $examens = [
                'test' => ['titre' => 'Examen Test', 'duree_minutes' => 90, 'nb_tentatives_max' => 9999, 'nb_questions' => 50],
                'final' => ['titre' => 'Examen Final', 'duree_minutes' => 120, 'nb_tentatives_max' => 1, 'nb_questions' => 100],
            ];
            foreach ($examens as $type => $params) {
                $examen = Examen::updateOrCreate(
                    ['formation_id' => $formation->id, 'type' => $type],
                    $params + ['score_minimum' => 70, 'delai_entre_tentatives_heures' => 24]
                );
                $examen->questions()->get()->each->delete();
                $this->questions($examen, $data['examen_questions']);
                $examen->update(['nb_questions' => min($params['nb_questions'], count($data['examen_questions']))]);
            }

            $this->info(sprintf(
                '%s : %d modules, %d lecons, %d questions par examen.',
                $formation->titre,
                $formation->modules()->count(),
                $formation->modules()->withCount('lecons')->get()->sum('lecons_count'),
                count($data['examen_questions'])
            ));
        });

        return self::SUCCESS;
    }

    /** @param \Illuminate\Database\Eloquent\Model $cible (Quiz|Examen) */
    private function questions($cible, array $questions): void
    {
        $type = $cible::class;
        $now = now();
        foreach ($questions as $i => $q) {
            $id = DB::table('questions')->insertGetId([
                'quizzable_type' => $type,
                'quizzable_id' => $cible->id,
                'texte' => $q['texte'],
                'type' => $q['type'] ?? 'qcm',
                'ordre' => $i + 1,
                'points' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            DB::table('reponses')->insert(array_map(fn ($r) => [
                'question_id' => $id,
                'texte' => $r['texte'],
                'is_correct' => (bool) $r['is_correct'],
                'created_at' => $now,
                'updated_at' => $now,
            ], $q['reponses']));
        }
    }
}
