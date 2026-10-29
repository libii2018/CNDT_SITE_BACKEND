<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Lien vers la fiche personnelle d'un membre (carte cliquable de la page
     * personnel.html) — ex : membres/ele-abiama-patrice.html.
     */
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->string('page_url')->nullable()->after('photo')
                ->comment('Lien de la fiche personnelle (cartes)');
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn('page_url');
        });
    }
};
