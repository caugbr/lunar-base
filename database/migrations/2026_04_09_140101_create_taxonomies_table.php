<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('taxonomies', function (Blueprint $table) {
            $table->id();
            $table->string('name');              // "Categorias", "Tags", "Assuntos"
            $table->string('slug')->unique();    // "categorias", "tags", "assuntos"
            $table->text('description')->nullable();
            $table->boolean('hierarchical')->default(false); // se pode ter pai→filho
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taxonomies');
    }
};
