<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->string('author_name')->nullable()->after('cover_image');
            $table->string('author_role')->nullable()->after('author_name');
            $table->string('author_photo')->nullable()->after('author_role');
            $table->string('author_url')->nullable()->after('author_photo');
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn(['author_name', 'author_role', 'author_photo', 'author_url']);
        });
    }
};
