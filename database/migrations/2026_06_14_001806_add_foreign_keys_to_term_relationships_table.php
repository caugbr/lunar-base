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
        Schema::table('term_relationships', function (Blueprint $table) {
            $table->foreign(['page_id'])->references(['id'])->on('pages')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['term_id'])->references(['id'])->on('terms')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('term_relationships', function (Blueprint $table) {
            $table->dropForeign('term_relationships_page_id_foreign');
            $table->dropForeign('term_relationships_term_id_foreign');
        });
    }
};
