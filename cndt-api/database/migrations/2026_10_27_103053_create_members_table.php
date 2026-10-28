<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('slug')->unique();
            $table->string('role')->nullable();           // fonction
            $table->string('unit')->nullable();           // unité / commission de rattachement
            $table->string('chapter')->nullable();         // chapitre (I, II, III)
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('photo')->nullable();
            $table->text('biography')->nullable();
            $table->boolean('is_visible')->default(true)->index();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
