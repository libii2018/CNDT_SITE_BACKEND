<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizational_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('organizational_units')->nullOnDelete();
            $table->string('code');            // I, I.1, II.1.5, 01...
            $table->string('label');
            $table->string('slug')->nullable()->unique();
            $table->string('url')->nullable(); // page vitrine correspondante
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organizational_units');
    }
};
