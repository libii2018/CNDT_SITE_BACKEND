<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Catalogue du personnel (page personnel.html) : un membre est affiché
     * soit en CARTE (niveaux hiérarchiques : photo + fonction + diplôme),
     * soit en LIGNE du tableau des personnels (n°, grade, spécialité).
     */
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->string('display_type', 20)->default('card')->after('slug')
                ->comment('card = carte hiérarchique, table = tableau des personnels');
            $table->string('level')->nullable()->after('display_type')
                ->comment('Niveau hiérarchique (cartes uniquement, ex : Direction)');
            $table->integer('level_position')->default(0)->after('level')
                ->comment('Ordre d\'affichage du niveau');
            $table->string('grade')->nullable()->after('role')
                ->comment('Grade (tableau uniquement, ex : Attaché de Recherche)');
            $table->string('degree')->nullable()->after('grade')
                ->comment('Diplôme affiché sur la carte');
            $table->string('speciality')->nullable()->after('degree')
                ->comment('Diplôme de recrutement / spécialité (tableau uniquement)');
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn(['display_type', 'level', 'level_position', 'grade', 'degree', 'speciality']);
        });
    }
};
