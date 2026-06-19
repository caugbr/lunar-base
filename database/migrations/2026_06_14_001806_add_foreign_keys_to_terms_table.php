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
        Schema::table('terms', function (Blueprint $table) {
            $table->foreign(['parent_id'])->references(['id'])->on('terms')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['taxonomy_id'])->references(['id'])->on('taxonomies')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('terms', function (Blueprint $table) {
            $table->dropForeign('terms_parent_id_foreign');
            $table->dropForeign('terms_taxonomy_id_foreign');
        });
    }
};
