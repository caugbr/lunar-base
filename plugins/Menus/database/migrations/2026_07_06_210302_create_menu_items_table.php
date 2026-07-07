<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();

            // Relacionamento com o menu pai (cascata ao deletar)
            $table->foreignId('menu_id')->constrained('menus')->onDelete('cascade');

            // Auto-relacionamento para itens filhos (sub-menus aninhados)
            $table->unsignedBigInteger('parent_id')->nullable()->index();
            $table->foreign('parent_id')->references('id')->on('menu_items')->onDelete('cascade');

            $table->string('label')->nullable(); // Rótulo (se nulo, busca do model)
            $table->string('type'); // 'custom', 'page', 'post', 'term'
            $table->text('url')->nullable(); // Usado somente se for 'custom'

            // Colunas Polimórficas (vincula de forma segura a qualquer model do sistema)
            $table->string('model_type')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();

            $table->integer('order')->default(0); // Ordenação sequencial
            $table->string('target')->default('_self'); // '_self' ou '_blank'
            $table->string('class')->nullable(); // Classes CSS personalizadas

            $table->timestamps();

            $table->index(['model_type', 'model_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
