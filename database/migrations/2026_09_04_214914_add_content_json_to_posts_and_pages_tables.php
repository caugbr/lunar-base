<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Adiciona em POSTS
        Schema::table('posts', function (Blueprint $table) {
            $table->longText('content_json')->nullable()->after('content');
        });

        // Adiciona em PAGES
        Schema::table('pages', function (Blueprint $table) {
            $table->longText('content_json')->nullable()->after('content');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('content_json');
        });

        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn('content_json');
        });
    }
};
