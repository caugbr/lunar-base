<?php
// database/migrations/2026_06_15_000003_add_foreign_keys_to_posts_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->foreign('author_id')
                  ->references('id')
                  ->on('users')
                  ->onUpdate('no action')
                  ->onDelete('restrict');

            $table->foreign('thumbnail_id')
                  ->references('id')
                  ->on('media')
                  ->onUpdate('no action')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropForeign(['author_id']);
            $table->dropForeign(['thumbnail_id']);
        });
    }
};
