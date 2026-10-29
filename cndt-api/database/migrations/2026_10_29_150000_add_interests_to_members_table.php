<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fiche individuelle : centres d'intérêts / domaines d'expertise,
     * affichés en « chips » sur la fiche (comme le design d'origine).
     */
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->json('interests')->nullable()->after('biography')
                ->comment("Centres d'intérêts (liste, fiche individuelle)");
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn('interests');
        });
    }
};
