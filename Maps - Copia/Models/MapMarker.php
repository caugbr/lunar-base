<?php

namespace Plugins\Maps\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MapMarker extends Model
{
    protected $fillable = [
        'map_id',
        'title',
        'lat',
        'lng',
        'content',
        'color',
        'icon',
        'sort_order',
    ];

    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
        'sort_order' => 'integer',
    ];

    public function map(): BelongsTo
    {
        return $this->belongsTo(Map::class);
    }
}
