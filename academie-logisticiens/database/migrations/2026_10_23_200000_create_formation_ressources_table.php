<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('formation_ressources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formation_id')->constrained()->cascadeOnDelete();
            $table->string('titre');
            $table->string('type', 30)->default('fiche_entretien');
            $table->longText('contenu_html')->nullable();
            $table->string('fichier')->nullable();
            $table->string('nom_fichier')->nullable();
            $table->string('mime', 120)->nullable();
            $table->unsignedInteger('ordre')->default(0);
            $table->timestamps();

            $table->index(['formation_id', 'ordre']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('formation_ressources');
    }
};
