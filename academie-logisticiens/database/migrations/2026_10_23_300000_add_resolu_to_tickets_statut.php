<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE tickets MODIFY statut ENUM('ouvert','en_cours','resolu','ferme') NOT NULL DEFAULT 'ouvert'");
    }

    public function down(): void
    {
        DB::statement("UPDATE tickets SET statut = 'ferme' WHERE statut = 'resolu'");
        DB::statement("ALTER TABLE tickets MODIFY statut ENUM('ouvert','en_cours','ferme') NOT NULL DEFAULT 'ouvert'");
    }
};
