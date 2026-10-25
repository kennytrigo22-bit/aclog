<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@academie.test'],
            [
                'name' => 'AMBIA',
                'prenom' => 'Didi Chance',
                'password' => bcrypt('password'),
                'telephone' => '+242 068 287 915',
                'role' => 'admin',
                'ville' => 'Brazzaville',
                'pays' => 'Congo',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'gestionnaire@academie.test'],
            [
                'name' => 'MOUKALA',
                'prenom' => 'Grâce',
                'password' => bcrypt('password'),
                'telephone' => '+242 05 599 73 34',
                'role' => 'gestionnaire',
                'ville' => 'Brazzaville',
                'pays' => 'Congo',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
