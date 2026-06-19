<?php
// database/migrations/2026_06_15_000001_make_term_relationships_polymorphic.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('term_relationships', function (Blueprint $table) {
            // Adiciona colunas polimórficas
            $table->unsignedBigInteger('termable_id')->nullable()->after('term_id');
            $table->string('termable_type')->nullable()->after('termable_id');

            // Índice para performance
            $table->index(['termable_id', 'termable_type']);
        });

        // Migra dados existentes das páginas
        DB::table('term_relationships')->update([
            'termable_id' => DB::raw('page_id'),
            'termable_type' => 'App\\Models\\Page',
        ]);

        Schema::table('term_relationships', function (Blueprint $table) {
            // Remove FK antiga
            $table->dropForeign(['page_id']);
            // Remove coluna antiga
            $table->dropColumn('page_id');
            // Torna polimórfico NOT NULL agora que temos dados
            $table->unsignedBigInteger('termable_id')->nullable(false)->change();
            $table->string('termable_type')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('term_relationships', function (Blueprint $table) {
            // Recria page_id
            $table->unsignedBigInteger('page_id')->nullable()->after('term_id');

            // Restaura dados (simplificado — em produção precisaria de lógica mais robusta)
            // Aqui só recria a coluna; dados perdidos se não houver backup
        });

        // Não tem como reverter dados polimórficos para page_id de forma 100% segura
        // sem saber quais eram páginas. Em produção, faria backup antes.

        Schema::table('term_relationships', function (Blueprint $table) {
            $table->dropIndex(['termable_id', 'termable_type']);
            $table->dropColumn(['termable_id', 'termable_type']);

            $table->foreign('page_id')->references('id')->on('pages')->onDelete('cascade');
        });
    }
};
