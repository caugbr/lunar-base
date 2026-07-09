<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maps', function (Blueprint $table) {
            // Dimensão / apresentação
            $table->unsignedInteger('width')->default(800)->after('height');
            $table->boolean('fullwidth')->default(true)->after('width');

            // GeoJSON — id do lugar no índice OU JSON inline (colado no textarea)
            $table->string('geojson_place', 128)->nullable()->after('allow_scroll_zoom');
            $table->json('geojson_inline')->nullable()->after('geojson_place');

            // Estilo do GeoJSON
            $table->string('geojson_color', 16)->default('#ff7800')->after('geojson_inline');
            $table->unsignedTinyInteger('geojson_weight')->default(3)->after('geojson_color');
            $table->decimal('geojson_opacity', 3, 2)->default(0.80)->after('geojson_weight');
            $table->string('geojson_fill_color', 16)->default('#ffa500')->after('geojson_opacity');
            $table->decimal('geojson_fill_opacity', 3, 2)->default(0.20)->after('geojson_fill_color');
        });

        Schema::table('map_markers', function (Blueprint $table) {
            // Identificador estável do marker (usado no admin JS igual ao WP)
            $table->string('uid', 32)->nullable()->after('id')->index();
            // Parâmetros extras key=value (URL-encoded ou JSON)
            $table->json('parameters')->nullable()->after('color');
        });
    }

    public function down(): void
    {
        Schema::table('map_markers', function (Blueprint $table) {
            $table->dropColumn(['uid', 'parameters']);
        });

        Schema::table('maps', function (Blueprint $table) {
            $table->dropColumn([
                'width', 'fullwidth',
                'geojson_place', 'geojson_inline',
                'geojson_color', 'geojson_weight', 'geojson_opacity',
                'geojson_fill_color', 'geojson_fill_opacity',
            ]);
        });
    }
};
