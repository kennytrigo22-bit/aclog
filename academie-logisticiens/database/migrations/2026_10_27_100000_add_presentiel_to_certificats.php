<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Certificats delivres aux apprenants formes en presentiel : pas forcement de compte sur la plateforme,
 * l'identite du titulaire (et au besoin l'intitule de la formation) est alors stockee sur le certificat.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificats', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
            $table->foreignId('formation_id')->nullable()->change();
            $table->string('mode', 20)->default('en_ligne')->after('formation_id');
            $table->string('titulaire_nom')->nullable()->after('mode');
            $table->string('titulaire_prenom')->nullable()->after('titulaire_nom');
            $table->string('titulaire_pays', 100)->nullable()->after('titulaire_prenom');
            $table->date('titulaire_date_naissance')->nullable()->after('titulaire_pays');
            $table->string('titulaire_lieu_naissance')->nullable()->after('titulaire_date_naissance');
            $table->string('titulaire_photo')->nullable()->after('titulaire_lieu_naissance');
            $table->string('formation_titre')->nullable()->after('titulaire_photo');
            $table->unsignedInteger('duree_heures')->nullable()->after('formation_titre');
            $table->date('date_debut')->nullable()->after('duree_heures');
            $table->date('date_fin')->nullable()->after('date_debut');
            $table->boolean('is_public')->default(true)->change();
        });

        // L'annuaire affiche desormais tous les certifies ; un apprenant peut toujours se masquer depuis son espace.
        DB::table('certificats')->update(['is_public' => true]);
    }

    public function down(): void
    {
        DB::table('certificats')->whereNull('user_id')->orWhereNull('formation_id')->delete();

        Schema::table('certificats', function (Blueprint $table) {
            $table->dropColumn([
                'mode', 'titulaire_nom', 'titulaire_prenom', 'titulaire_pays', 'titulaire_date_naissance',
                'titulaire_lieu_naissance', 'titulaire_photo', 'formation_titre', 'duree_heures', 'date_debut', 'date_fin',
            ]);
            $table->foreignId('user_id')->nullable(false)->change();
            $table->foreignId('formation_id')->nullable(false)->change();
            $table->boolean('is_public')->default(false)->change();
        });
    }
};
