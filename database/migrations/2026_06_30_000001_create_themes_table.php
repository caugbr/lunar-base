<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('themes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('folder_name')->unique(); // e.g., 'ThemeLavender'
            $table->string('version')->nullable();
            $table->text('description')->nullable();
            $table->string('author')->nullable();
            $table->string('screenshot')->nullable(); // e.g., 'assets/preview.png'
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('themes');
    }
};
