<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('map_markers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('map_id')->constrained('maps')->cascadeOnDelete();
            $table->string('title');
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            $table->text('content')->nullable();
            $table->string('color')->default('#e74c3c');
            $table->string('icon')->default('map-pin'); // lucide icon name
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('map_markers');
    }
};
