<?php
// database/migrations/2026_06_20_000004_create_post_meta_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_meta', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('post_id')->index();
            $table->string('meta_key')->index();
            $table->longText('meta_value')->nullable();

            $table->timestamps();

            // Chave única para evitar duplicatas do mesmo meta_key por post
            $table->unique(['post_id', 'meta_key']);

            // Foreign key
            $table->foreign('post_id')
                  ->references('id')
                  ->on('posts')
                  ->onUpdate('no action')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_meta');
    }
};
