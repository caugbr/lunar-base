<?php

namespace Plugins\Banners\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BannerClick extends Model
{
    protected $table = 'banner_clicks';

    protected $fillable = [
        'banner_id', 'clicked_at', 'ip_address', 'user_agent'
    ];

    protected $casts = [
        'clicked_at' => 'datetime',
    ];

    public function banner(): BelongsTo
    {
        return $this->belongsTo(Banner::class, 'banner_id');
    }
}
