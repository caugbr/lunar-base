<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('term_relationships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('term_id')->constrained()->onDelete('cascade');
            $table->foreignId('page_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['term_id', 'page_id']); // Evita duplicidade
            $table->index('page_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('term_relationships');
    }
};
