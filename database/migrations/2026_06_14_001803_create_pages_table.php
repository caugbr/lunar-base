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
        Schema::create('pages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->string('slug');
            $table->string('namespace')->nullable();
            $table->longText('content');
            $table->text('excerpt')->nullable();
            $table->unsignedBigInteger('author_id')->index('pages_author_id_foreign');
            $table->string('status')->default('draft')->index();
            $table->string('template');
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('thumbnail_id')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
