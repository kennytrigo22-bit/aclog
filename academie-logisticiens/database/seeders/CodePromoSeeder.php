<?php

namespace Database\Seeders;

use App\Models\CodePromo;
use Illuminate\Database\Seeder;

class CodePromoSeeder extends Seeder
{
    public function run(): void
    {
        CodePromo::firstOrCreate(['code' => 'BIENVENUE2026'], [
            'type' => 'pourcentage',
            'valeur' => 10,
            'perimetre' => 'all',
            'date_debut' => now(),
            'date_fin' => now()->addMonths(6),
            'nb_utilisations_max' => 100,
            'is_active' => true,
            'created_by' => 1,
        ]);

        CodePromo::firstOrCreate(['code' => 'DOUANE5000'], [
            'type' => 'fixe',
            'valeur' => 5000,
            'perimetre' => 'categorie',
            'categorie' => 'douane',
            'date_debut' => now(),
            'date_fin' => now()->addMonths(3),
            'nb_utilisations_max' => 50,
            'is_active' => true,
            'created_by' => 1,
        ]);

        CodePromo::firstOrCreate(['code' => 'LOGISTIQUE20'], [
            'type' => 'pourcentage',
            'valeur' => 20,
            'perimetre' => 'categorie',
            'categorie' => 'logistique',
            'date_debut' => now(),
            'date_fin' => now()->addMonths(2),
            'nb_utilisations_max' => 30,
            'is_active' => true,
            'created_by' => 1,
        ]);
    }
}
