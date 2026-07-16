<?php

namespace Plugins\Banners\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Banner extends Model
{
    protected $table = 'banners';

    protected $fillable = [
        'title', 'slug', 'image_id', 'link_url', 'hook',
        'is_active', 'target', 'class', 'clicks'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'clicks' => 'integer',
    ];

    /**
     * Estatisticas de cliques detalhadas
     */
    public function clickStats(): HasMany
    {
        return $this->hasMany(BannerClick::class, 'banner_id')
            ->orderBy('clicked_at', 'desc');
    }

    /**
     * Relacao com a midia do core
     */
    public function image(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Media::class, 'image_id');
    }

    /**
     * URL da imagem via relacionamento ou fallback
     */
    public function getImageUrlAttribute(): string
    {
        if ($this->image) {
            return $this->image->url;
        }
        return '';
    }

    /**
     * Incrementa o contador total de cliques
     */
    public function incrementClicks(): void
    {
        $this->increment('clicks');
    }

    /**
     * Registra um clique detalhado com timestamp
     */
    public function recordClick(): void
    {
        $this->incrementClicks();

        $this->clickStats()->create([
            'clicked_at' => now(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Estatisticas agregadas por periodo
     */
    public function getStatsByPeriod(string $period = 'day'): array
    {
        $format = match ($period) {
            'hour' => '%Y-%m-%d %H:00',
            'day' => '%Y-%m-%d',
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            default => '%Y-%m-%d',
        };

        return $this->clickStats()
            ->reorder()
            ->selectRaw("DATE_FORMAT(clicked_at, '{$format}') as period, COUNT(*) as total")
            ->groupBy('period')
            ->orderBy('period', 'asc')
            ->pluck('total', 'period')
            ->toArray();
    }
}
