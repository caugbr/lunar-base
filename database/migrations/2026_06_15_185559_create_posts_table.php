<?php
// database/migrations/2026_06_15_000002_create_posts_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('title');
            $table->string('slug');

            $table->longText('content');
            $table->text('excerpt')->nullable();

            $table->unsignedBigInteger('author_id')->index();
            $table->string('status')->default('draft')->index();

            // Template (mesmo conceito das páginas, mas busca em post-templates/)
            $table->string('template');

            // Destaques
            $table->boolean('featured')->default(false)->index();
            $table->boolean('sticky')->default(false)->index();

            // Agendamento
            $table->timestamp('published_at')->nullable()->index();

            // Thumbnail
            $table->unsignedBigInteger('thumbnail_id')->nullable()->index();

            $table->timestamps();
            $table->softDeletes();

            // Slug único global (como no WordPress)
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
