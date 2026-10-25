<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tentatives_examen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('examen_id')->constrained()->cascadeOnDelete();
            $table->integer('score')->default(0);
            $table->json('reponses_donnees')->nullable();
            $table->boolean('is_passed')->default(false);
            $table->integer('numero_tentative')->default(1);
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tentatives_examen');
    }
};
