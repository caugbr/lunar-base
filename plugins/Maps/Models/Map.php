<?php

namespace Plugins\Maps\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Map extends Model
{
    protected $table = 'maps';

    protected $fillable = [
        'title',
        'slug',
        'description',
        'center_lat',
        'center_lng',
        'zoom',
        'width',
        'height',
        'fullwidth',
        'show_zoom_controls',
        'allow_drag',
        'allow_scroll_zoom',
        'geojson_place',
        'geojson_inline',
        'geojson_color',
        'geojson_weight',
        'geojson_opacity',
        'geojson_fill_color',
        'geojson_fill_opacity',
    ];

    protected $casts = [
        'center_lat'           => 'float',
        'center_lng'           => 'float',
        'zoom'                 => 'integer',
        'width'                => 'integer',
        'height'               => 'integer',
        'fullwidth'            => 'boolean',
        'show_zoom_controls'   => 'boolean',
        'allow_drag'           => 'boolean',
        'allow_scroll_zoom'    => 'boolean',
        'geojson_inline'       => 'array',
        'geojson_weight'       => 'integer',
        'geojson_opacity'      => 'float',
        'geojson_fill_opacity' => 'float',
    ];

    public function markers(): HasMany
    {
        return $this->hasMany(MapMarker::class)->orderBy('title');
    }

    /**
     * Estilo do GeoJSON no formato esperado pelo Leaflet.
     */
    public function geojsonStyle(): array
    {
        return [
            'color'       => $this->geojson_color ?: '#ff7800',
            'weight'      => (int) ($this->geojson_weight ?: 3),
            'opacity'     => (float) ($this->geojson_opacity ?: 0.8),
            'fillColor'   => $this->geojson_fill_color ?: '#ffa500',
            'fillOpacity' => (float) ($this->geojson_fill_opacity ?: 0.2),
        ];
    }
}
