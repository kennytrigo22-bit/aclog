<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('formations', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->string('slug')->unique();
            $table->text('description');
            $table->longText('programme')->nullable();
            $table->enum('categorie', ['logistique', 'commerce', 'douane']);
            $table->enum('niveau', ['debutant', 'intermediaire', 'avance']);
            $table->integer('prix');
            $table->integer('prix_promo')->nullable();
            $table->boolean('promo_active')->default(false);
            $table->integer('duree_heures')->default(0);
            $table->string('image')->nullable();
            $table->string('video_intro')->nullable();
            $table->boolean('is_published')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->enum('badge', ['none', 'populaire', 'nouveau'])->default('none');
            $table->integer('ordre')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('formations');
    }
};
