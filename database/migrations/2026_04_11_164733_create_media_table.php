<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();

            // Dados do arquivo
            $table->string('name');              // Nome original (ex: foto-familia.jpg)
            $table->string('path')->unique();    // Caminho relativo no storage (ex: media/2026/04/uuid.jpg)
            $table->string('mime_type');         // application/pdf, image/jpeg, etc.
            $table->unsignedBigInteger('size');  // Tamanho em bytes
            $table->unsignedInteger('width')->nullable();  // px (apenas imagens)
            $table->unsignedInteger('height')->nullable(); // px (apenas imagens)

            // Metadados
            $table->string('alt')->nullable();           // Texto alternativo (SEO/Acessibilidade)
            $table->string('caption')->nullable();       // Legenda/descrição curta
            $table->string('hash')->nullable()->index(); // SHA-256 ou MD5 (para evitar duplicatas futuras)

            // Relacionamento polimórfico (nullable = mídia "solta" na biblioteca)
            $table->nullableMorphs('mediaable'); // mediaable_id + mediaable_type

            $table->timestamps();
            $table->softDeletes(); // Lixeira: permite recuperar ou limpar depois
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
