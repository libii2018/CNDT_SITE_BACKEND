<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->boolean('in_hero')->default(false)->index()->after('author_url');
            $table->unsignedInteger('hero_position')->nullable()->index()->after('in_hero');
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn(['in_hero', 'hero_position']);
        });
    }
};
