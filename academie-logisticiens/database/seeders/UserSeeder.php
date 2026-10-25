<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $noms = [
            ['MAKOSSO', 'Arsène'], ['NGOULOU', 'Béatrice'], ['MBEMBA', 'Christian'],
            ['ONDONGO', 'Diane'], ['MOUANDA', 'Éric'], ['BANZOUZI', 'Francine'],
            ['NZABA', 'Ghislain'], ['MASSAMBA', 'Hortense'], ['OKEMBA', 'Ibrahim'],
            ['LOEMBA', 'Joséphine'], ['MPASSI', 'Kevin'], ['NKOUKA', 'Lydie'],
            ['MALONGA', 'Marcel'], ['OBAMBI', 'Nadège'], ['PANGOU', 'Olivier'],
            ['SAMBA', 'Pierrette'], ['TATY', 'Rodrigue'], ['VOUAMA', 'Sylvie'],
            ['YOKA', 'Thierry'], ['ZINGA', 'Ursule'],
        ];

        $villes = ['Brazzaville', 'Pointe-Noire', 'Dolisie', 'Nkayi', 'Owando'];
        $niveaux = ['Bac', 'Bac+2', 'Bac+3', 'Bac+5', 'Master', 'Doctorat'];
        $professions = [
            'Étudiant', 'Transitaire', 'Agent commercial', 'Déclarant en douane',
            'Magasinier', 'Responsable logistique', 'Transporteur', 'Commerçant',
            'Fonctionnaire', 'Entrepreneur',
        ];
        $sources = ['Réseaux sociaux', 'Recherche Google', 'Bouche à oreille', 'Publicité', 'Partenaire'];

        foreach ($noms as $i => [$nom, $prenom]) {
            User::firstOrCreate(
                ['email' => strtolower($prenom) . '.' . strtolower($nom) . '@example.com'],
                [
                'name' => $nom,
                'prenom' => $prenom,
                'password' => bcrypt('password'),
                'telephone' => '+242 0' . rand(5, 6) . ' ' . rand(100, 999) . ' ' . rand(10, 99) . ' ' . rand(10, 99),
                'date_naissance' => now()->subYears(rand(20, 45))->subDays(rand(0, 365)),
                'niveau_etudes' => $niveaux[array_rand($niveaux)],
                'profession' => $professions[array_rand($professions)],
                'ville' => $villes[array_rand($villes)],
                'pays' => 'Congo',
                'points' => rand(0, 80),
                'jetons' => rand(0, 2) * 5000,
                'source_decouverte' => $sources[array_rand($sources)],
                'accept_marketing' => (bool) rand(0, 1),
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
        }
    }
}
