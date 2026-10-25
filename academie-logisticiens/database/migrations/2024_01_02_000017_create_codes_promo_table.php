<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('codes_promo', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->enum('type', ['pourcentage', 'fixe']);
            $table->integer('valeur');
            $table->enum('perimetre', ['all', 'categorie', 'formation'])->default('all');
            $table->foreignId('formation_id')->nullable()->constrained()->nullOnDelete();
            $table->string('categorie')->nullable();
            $table->timestamp('date_debut')->nullable();
            $table->timestamp('date_fin')->nullable();
            $table->integer('nb_utilisations_max')->nullable();
            $table->integer('nb_utilisations_actuel')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('codes_promo');
    }
};
