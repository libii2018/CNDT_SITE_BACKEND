<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fiche individuelle automatique des membres « cartes » : année d'entrée
     * en fonction affichée dans les informations clés (« Depuis »).
     */
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->string('since')->nullable()->after('speciality')
                ->comment("Année d'entrée en fonction (fiche individuelle)");
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn('since');
        });
    }
};
