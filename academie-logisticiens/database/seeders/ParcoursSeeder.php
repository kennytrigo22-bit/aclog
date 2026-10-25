<?php

namespace Database\Seeders;

use App\Models\Challenge;
use App\Models\Parcours;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ParcoursSeeder extends Seeder
{
    public function run(): void
    {
        $domaines = [
            'transport' => 'Transport',
            'logistique' => 'Logistique',
            'transport_logistique' => 'Transport & Logistique',
            'commerce' => 'Commerce International',
            'douane' => 'Douane et Transit',
        ];

        $niveaux = ['debutant', 'intermediaire', 'avance'];
        $ordre = 1;

        foreach ($domaines as $domaine => $label) {
            foreach ($niveaux as $niveau) {
                $niveauLabel = match($niveau) {
                    'debutant' => 'Débutant',
                    'intermediaire' => 'Intermédiaire',
                    'avance' => 'Avancé',
                };

                $slug = Str::slug("{$label} {$niveauLabel}");
                if (Parcours::where('slug', $slug)->exists()) { continue; }

                $parcours = Parcours::create([
                    'titre' => "{$label} — Niveau {$niveauLabel}",
                    'slug' => $slug,
                    'description' => "Parcours de challenges en {$label} pour le niveau {$niveauLabel}. Testez vos connaissances et gagnez des points !",
                    'domaine' => $domaine,
                    'niveau' => $niveau,
                    'is_active' => true,
                    'ordre' => $ordre++,
                ]);

                $this->creerChallenges($parcours, $domaine);
            }
        }
    }

    private function creerChallenges(Parcours $parcours, string $domaine): void
    {
        $challenges = $this->getChallenges($domaine, $parcours->niveau);

        foreach ($challenges as $i => $ch) {
            Challenge::create([
                'parcours_id' => $parcours->id,
                'titre' => $ch['titre'],
                'description' => $ch['description'],
                'flag_attendu' => $ch['flag'],
                'indice' => $ch['indice'],
                'points' => 5,
                'ordre' => $i + 1,
                'is_active' => true,
            ]);
        }
    }

    private function getChallenges(string $domaine, string $niveau): array
    {
        $base = [
            ['titre' => 'Vocabulaire de base', 'description' => 'Quel terme désigne l\'ensemble des activités de planification, exécution et contrôle du mouvement des marchandises ?', 'flag' => 'FLAG{logistique}', 'indice' => 'C\'est le nom de notre discipline principale.'],
            ['titre' => 'Acteurs de la chaîne', 'description' => 'Identifiez l\'acteur intermédiaire entre l\'exportateur et l\'importateur qui organise le transport.', 'flag' => 'FLAG{transitaire}', 'indice' => 'Il transite entre les deux parties.'],
            ['titre' => 'Documents essentiels', 'description' => 'Quel document atteste de la prise en charge des marchandises par le transporteur maritime ?', 'flag' => 'FLAG{connaissement}', 'indice' => 'Bill of Lading en anglais.'],
            ['titre' => 'Zones économiques', 'description' => 'Quel est l\'acronyme de la communauté économique regroupant les pays d\'Afrique Centrale ?', 'flag' => 'FLAG{cemac}', 'indice' => '6 pays membres dont le Congo.'],
            ['titre' => 'Calcul logistique', 'description' => 'Si un conteneur 20 pieds peut contenir 28 CBM et que chaque carton fait 0.07 CBM, combien de cartons peut-on charger ?', 'flag' => 'FLAG{400}', 'indice' => 'Divisez le volume total par le volume unitaire.'],
        ];

        return $base;
    }
}
