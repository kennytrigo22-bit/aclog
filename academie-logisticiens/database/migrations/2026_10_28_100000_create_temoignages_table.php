<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('temoignages', function (Blueprint $table) {
            $table->id();
            $table->text('citation');
            $table->string('nom');
            $table->string('fonction')->nullable();
            $table->string('photo')->nullable();
            $table->unsignedInteger('ordre')->default(0);
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });

        // Temoignages affiches jusqu'ici en dur sur l'accueil : repris tels quels pour que le site ne change pas.
        $maintenant = now();
        DB::table('temoignages')->insert([
            ['citation' => "Grâce au module sur les régimes douaniers suspensifs CEMAC et aux simulations SYDONIA, j'ai réussi mon entretien chez Bolloré Transport & Logistics à Pointe-Noire dès le mois suivant ma certification.", 'nom' => 'Cedric Nguesso', 'fonction' => 'Déclarant en Douane Senior • Pointe-Noire', 'ordre' => 1, 'actif' => true, 'created_at' => $maintenant, 'updated_at' => $maintenant],
            ['citation' => "La flexibilité de la plateforme m'a permis d'étudier le soir tout en conservant mon poste de gestionnaire de stocks à Maya-Maya. Le paiement par Airtel Money était fluide et immédiat.", 'nom' => 'Clarisse Moukala', 'fonction' => 'Superviseur Logistique • Brazzaville', 'ordre' => 2, 'actif' => true, 'created_at' => $maintenant, 'updated_at' => $maintenant],
            ['citation' => "Les jetons FCFA gagnés via les challenges m'ont fait économiser 15 000 FCFA sur mon certificat international Incoterms. C'est l'école la plus innovante d'Afrique Centrale.", 'nom' => 'Jean-Paul Samba', 'fonction' => "Directeur d'Agence Transit • Kinshasa / BZV", 'ordre' => 3, 'actif' => true, 'created_at' => $maintenant, 'updated_at' => $maintenant],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('temoignages');
    }
};
