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
        Schema::table('pages', function (Blueprint $table) {
            $table->foreign(['author_id'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('restrict');
            $table->foreign(['thumbnail_id'])->references(['id'])->on('media')->onUpdate('no action')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropForeign('pages_author_id_foreign');
            $table->dropForeign('pages_thumbnail_id_foreign');
        });
    }
};
