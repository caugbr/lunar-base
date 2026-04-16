<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            // 👇 Nova coluna: referência à imagem destacada
            $table->foreignId('thumbnail_id')
                  ->nullable()                    // Página pode não ter thumbnail
                  ->constrained('media')          // FK para media.id
                  ->nullOnDelete();               // Se a mídia for excluída, thumbnail_id vira NULL (não quebra a página)

            // 👇 Índice para performance em consultas
            $table->index('thumbnail_id');
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            // Remove índice e FK na rollback
            $table->dropForeign(['thumbnail_id']);
            $table->dropIndex(['thumbnail_id']);
            $table->dropColumn('thumbnail_id');
        });
    }
};
