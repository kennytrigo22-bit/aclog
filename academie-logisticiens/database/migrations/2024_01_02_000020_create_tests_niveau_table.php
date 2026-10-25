<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tests_niveau', function (Blueprint $table) {
            $table->id();
            $table->string('domaine');
            $table->string('niveau');
            $table->string('titre');
            $table->integer('nb_questions')->default(10);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['domaine', 'niveau']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tests_niveau');
    }
};
