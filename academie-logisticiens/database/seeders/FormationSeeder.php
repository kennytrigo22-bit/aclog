<?php

namespace Database\Seeders;

use App\Models\Examen;
use App\Models\Formation;
use App\Models\Lecon;
use App\Models\Module;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\Reponse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FormationSeeder extends Seeder
{
    public function run(): void
    {
        $formations = [
            [
                'titre' => 'Fondamentaux de la Logistique et Supply Chain',
                'categorie' => 'logistique', 'niveau' => 'debutant',
                'prix' => 35000, 'duree_heures' => 40,
                'badge' => 'populaire', 'is_featured' => true,
                'description' => 'Découvrez les bases de la logistique moderne : gestion des stocks, entreposage, transport et distribution. Formation idéale pour débuter une carrière dans la supply chain en Afrique Centrale.',
            ],
            [
                'titre' => 'Gestion Avancée des Opérations Logistiques',
                'categorie' => 'logistique', 'niveau' => 'avance',
                'prix' => 65000, 'duree_heures' => 60,
                'badge' => 'none', 'is_featured' => false,
                'description' => 'Maîtrisez les techniques avancées de gestion logistique : optimisation des flux, lean management, KPI logistiques et gestion de projets supply chain.',
            ],
            [
                'titre' => 'Commerce International et Incoterms® 2020',
                'categorie' => 'commerce', 'niveau' => 'intermediaire',
                'prix' => 45000, 'duree_heures' => 35,
                'badge' => 'nouveau', 'is_featured' => true,
                'description' => 'Comprenez les mécanismes du commerce international : Incoterms® 2020, documents commerciaux, assurance transport et financement des échanges dans la zone CEMAC.',
            ],
            [
                'titre' => 'Techniques de Négociation et Achat International',
                'categorie' => 'commerce', 'niveau' => 'avance',
                'prix' => 55000, 'duree_heures' => 45,
                'badge' => 'none', 'is_featured' => false,
                'description' => 'Perfectionnez vos compétences en négociation commerciale internationale : stratégies d\'achat, gestion des fournisseurs et contrats internationaux.',
            ],
            [
                'titre' => 'Procédures Douanières et Transit CEMAC',
                'categorie' => 'douane', 'niveau' => 'debutant',
                'prix' => 40000, 'duree_heures' => 50,
                'badge' => 'populaire', 'is_featured' => true,
                'description' => 'Apprenez les procédures douanières en zone CEMAC : déclaration en douane, tarif extérieur commun, régimes douaniers et système SYDONIA World.',
            ],
            [
                'titre' => 'Expertise en Réglementation Douanière et Contentieux',
                'categorie' => 'douane', 'niveau' => 'avance',
                'prix' => 75000, 'duree_heures' => 55,
                'badge' => 'none', 'is_featured' => false,
                'description' => 'Devenez expert en réglementation douanière : contentieux douanier, infractions, sanctions, recours et audit douanier dans l\'espace CEMAC.',
                'is_published' => false,
            ],
        ];

        $moduleTitres = [
            'logistique' => [
                ['Introduction à la logistique', 'Gestion des stocks et entreposage', 'Transport et distribution', 'Pilotage de la performance'],
                ['Stratégie supply chain', 'Optimisation des flux', 'Lean logistics', 'Management de projets logistiques'],
            ],
            'commerce' => [
                ['Environnement du commerce international', 'Incoterms® 2020 en détail', 'Documents et paiements', 'Assurance et contentieux'],
                ['Stratégies d\'achat international', 'Négociation interculturelle', 'Contrats internationaux', 'Gestion des risques'],
            ],
            'douane' => [
                ['Introduction aux douanes CEMAC', 'Classement tarifaire et valeur', 'Régimes douaniers', 'SYDONIA World pratique'],
                ['Réglementation avancée', 'Contentieux douanier', 'Audit et conformité', 'Cas pratiques CEMAC'],
            ],
        ];

        $catIndex = ['logistique' => 0, 'commerce' => 0, 'douane' => 0];

        foreach ($formations as $ordre => $data) {
            $cat = $data['categorie'];
            $idx = $catIndex[$cat]++;

            $slug = Str::slug($data['titre']);
            if (Formation::where('slug', $slug)->exists()) { $catIndex[$cat]++; continue; }

            $formation = Formation::create([
                'titre' => $data['titre'],
                'slug' => $slug,
                'description' => $data['description'],
                'programme' => 'Programme détaillé de la formation ' . $data['titre'],
                'categorie' => $cat,
                'niveau' => $data['niveau'],
                'prix' => $data['prix'],
                'prix_promo' => ($ordre % 3 === 0) ? (int)($data['prix'] * 0.8) : null,
                'promo_active' => ($ordre % 3 === 0),
                'duree_heures' => $data['duree_heures'],
                'is_published' => $data['is_published'] ?? true,
                'is_featured' => $data['is_featured'],
                'badge' => $data['badge'],
                'ordre' => $ordre + 1,
            ]);

            $titresModules = $moduleTitres[$cat][$idx] ?? $moduleTitres[$cat][0];

            foreach ($titresModules as $mOrdre => $mTitre) {
                $module = Module::create([
                    'formation_id' => $formation->id,
                    'titre' => $mTitre,
                    'description' => "Module consacré à : {$mTitre}",
                    'ordre' => $mOrdre + 1,
                ]);

                for ($l = 1; $l <= 3; $l++) {
                    Lecon::create([
                        'module_id' => $module->id,
                        'titre' => "Leçon {$l} : " . $this->genererTitreLecon($mTitre, $l),
                        'description' => "Contenu pédagogique de la leçon {$l} du module {$mTitre}.",
                        'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                        'duree_minutes' => rand(15, 45),
                        'contenu_texte' => "Contenu textuel détaillé de la leçon. Ce texte sera remplacé par le contenu réel fourni par le client.",
                        'ordre' => $l,
                        'is_preview' => ($mOrdre === 0 && $l === 1),
                    ]);
                }

                $quiz = Quiz::create([
                    'module_id' => $module->id,
                    'titre' => "Quiz : {$mTitre}",
                ]);

                $this->creerQuestions($quiz, 5);
            }

            $examen = Examen::create([
                'formation_id' => $formation->id,
                'titre' => "Examen final : {$formation->titre}",
                'duree_minutes' => 80,
                'score_minimum' => 70,
                'nb_tentatives_max' => 1,
                'nb_questions' => 20,
            ]);

            $this->creerQuestions($examen, 20);
        }
    }

    private function creerQuestions($quizzable, int $nb): void
    {
        $sujets = [
            'Quelle est la définition correcte de ce concept ?',
            'Parmi les propositions suivantes, laquelle est vraie ?',
            'Quel est le rôle principal de cet élément ?',
            'Quelle affirmation est correcte concernant ce sujet ?',
            'Identifiez la bonne réponse parmi les options suivantes :',
        ];

        for ($i = 1; $i <= $nb; $i++) {
            $question = Question::create([
                'quizzable_type' => get_class($quizzable),
                'quizzable_id' => $quizzable->id,
                'texte' => $sujets[($i - 1) % count($sujets)] . " (Question {$i})",
                'type' => ($i % 5 === 0) ? 'vrai_faux' : 'qcm',
                'ordre' => $i,
                'points' => 1,
            ]);

            if ($question->type === 'vrai_faux') {
                $correct = (bool) rand(0, 1);
                Reponse::create(['question_id' => $question->id, 'texte' => 'Vrai', 'is_correct' => $correct]);
                Reponse::create(['question_id' => $question->id, 'texte' => 'Faux', 'is_correct' => !$correct]);
            } else {
                $correctIdx = rand(0, 3);
                for ($r = 0; $r < 4; $r++) {
                    Reponse::create([
                        'question_id' => $question->id,
                        'texte' => "Réponse " . chr(65 + $r) . " pour la question {$i}",
                        'is_correct' => ($r === $correctIdx),
                    ]);
                }
            }
        }
    }

    private function genererTitreLecon(string $module, int $num): string
    {
        $titres = [
            1 => ['Concepts fondamentaux', 'Définitions et périmètre', 'Introduction et contexte'],
            2 => ['Méthodologie et outils', 'Processus et étapes', 'Techniques appliquées'],
            3 => ['Études de cas pratiques', 'Mise en application', 'Synthèse et évaluation'],
        ];
        return $titres[$num][array_rand($titres[$num])];
    }
}
