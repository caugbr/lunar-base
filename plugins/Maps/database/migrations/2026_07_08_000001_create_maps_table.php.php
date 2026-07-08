<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maps', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->decimal('center_lat', 10, 7)->default(-23.5505);
            $table->decimal('center_lng', 10, 7)->default(-46.6333);
            $table->unsignedTinyInteger('zoom')->default(13);
            $table->unsignedSmallInteger('height')->default(400);
            $table->boolean('show_zoom_controls')->default(true);
            $table->boolean('allow_drag')->default(true);
            $table->boolean('allow_scroll_zoom')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maps');
    }
};
