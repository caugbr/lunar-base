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
        Schema::table('post_meta', function (Blueprint $table) {
            $table->foreign(['post_id'])->references(['id'])->on('posts')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('post_meta', function (Blueprint $table) {
            $table->dropForeign('post_meta_post_id_foreign');
        });
    }
};
