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
            $table->id();

            // Conteúdo da página
            $table->string('title');                    // Título da página
            $table->string('slug')->unique();           // URL amigável (ex: termos-de-uso)
            $table->longText('content');                // Conteúdo HTML (editor rich text)
            $table->text('excerpt')->nullable();        // Resumo/descrição curta (opcional)

            $table->foreignId('author_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');

            // Metadados
            $table->string('status')->default('draft');  // draft, published, archived
            $table->string('template')->default('page');

            // Controle
            $table->timestamps();
            $table->softDeletes();                      // Permite restaurar páginas

            // Índices
            $table->index('slug');
            $table->index('author_id');
            $table->index('status');
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
