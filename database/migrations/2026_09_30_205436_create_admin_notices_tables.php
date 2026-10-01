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
        // Tabela de Avisos Criados
        Schema::create('admin_notices', function (Blueprint $table) {
            $table->id();
            $table->text('message');
            $table->enum('type', ['info', 'warning', 'error', 'success'])->default('info');

            // Direcionamento: 'all' (todos), 'roles' (perfis específicos), 'users' (usuários específicos)
            $table->string('target_type', 20)->default('all');
            $table->json('target_values')->nullable(); // Ex: ["editor", "author"] ou [2, 5, 8]

            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('expires_at')->nullable()->index();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Tabela de Descarte (Quem já clicou no "X")
        Schema::create('admin_notice_dismissals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notice_id')->constrained('admin_notices')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('dismissed_at')->useCurrent();

            // Garante que o mesmo usuário só tem 1 descarte por aviso
            $table->unique(['notice_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_notice_dismissals');
        Schema::dropIfExists('admin_notices');
    }
};
