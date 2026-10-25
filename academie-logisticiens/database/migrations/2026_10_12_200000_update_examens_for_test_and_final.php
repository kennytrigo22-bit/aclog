<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('examens', 'type')) {
            Schema::table('examens', function (Blueprint $table) {
                $table->string('type', 10)->default('final')->after('formation_id');
            });
        }

        Schema::table('examens', function (Blueprint $table) {
            $table->dropForeign(['formation_id']);
        });

        DB::statement('ALTER TABLE examens DROP INDEX IF EXISTS examens_formation_id_unique');

        Schema::table('examens', function (Blueprint $table) {
            $table->foreign('formation_id')->references('id')->on('formations')->cascadeOnDelete();
            $table->unique(['formation_id', 'type']);
        });

        Schema::table('tentatives_examen', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('numero_tentative');
        });
    }

    public function down(): void
    {
        Schema::table('tentatives_examen', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });

        Schema::table('examens', function (Blueprint $table) {
            $table->dropUnique(['formation_id', 'type']);
            $table->dropColumn('type');
            $table->unique('formation_id');
        });
    }
};
