<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificats', function (Blueprint $table) {
            $table->string('numero')->nullable()->unique()->after('code_verification');
        });
    }

    public function down(): void
    {
        Schema::table('certificats', function (Blueprint $table) {
            $table->dropUnique(['numero']);
            $table->dropColumn('numero');
        });
    }
};
