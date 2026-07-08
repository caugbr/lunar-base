<?php

namespace Plugins\Maps\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Map extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'center_lat',
        'center_lng',
        'zoom',
        'height',
        'show_zoom_controls',
        'allow_drag',
        'allow_scroll_zoom',
        'description',
    ];

    protected $casts = [
        'center_lat' => 'float',
        'center_lng' => 'float',
        'zoom' => 'integer',
        'height' => 'integer',
        'show_zoom_controls' => 'boolean',
        'allow_drag' => 'boolean',
        'allow_scroll_zoom' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Map $map) {
            if (empty($map->slug)) {
                $map->slug = Str::slug($map->title);
            }
        });
    }

    public function markers(): HasMany
    {
        return $this->hasMany(MapMarker::class)->orderBy('sort_order');
    }
}
