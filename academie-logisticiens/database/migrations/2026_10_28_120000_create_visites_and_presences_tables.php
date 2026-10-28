<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Statistiques du tableau de bord admin : une ligne par page vue (visites) et la derniere activite
 * de chaque visiteur (presences, pour "en ligne maintenant"). Aucune IP n'est stockee.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visites', function (Blueprint $table) {
            $table->id();
            $table->char('visiteur', 40)->index();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('chemin', 255);
            $table->foreignId('formation_id')->nullable()->index();
            $table->char('pays', 2)->nullable()->index();
            $table->string('appareil', 12);
            $table->string('referent', 120)->nullable();
            $table->timestamp('created_at')->index();
        });

        Schema::create('presences', function (Blueprint $table) {
            $table->char('visiteur', 40)->primary();
            $table->foreignId('user_id')->nullable();
            $table->string('chemin', 255);
            $table->char('pays', 2)->nullable();
            $table->timestamp('derniere_activite')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presences');
        Schema::dropIfExists('visites');
    }
};
