<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('examens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formation_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('titre');
            $table->integer('duree_minutes')->default(80);
            $table->integer('score_minimum')->default(70);
            $table->integer('nb_tentatives_max')->default(1);
            $table->integer('delai_entre_tentatives_heures')->default(24);
            $table->integer('nb_questions')->default(100);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('examens');
    }
};
