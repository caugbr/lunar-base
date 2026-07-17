<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use App\Traits\HasMeta;

class Media extends Model
{
    use HasFactory, SoftDeletes, HasMeta;

    protected $fillable = [
        'name', 'path', 'mime_type', 'size', 'width', 'height',
        'alt', 'caption', 'hash',
        'meta'
    ];

    protected $casts = [
        'size'   => 'integer',
        'width'  => 'integer',
        'height' => 'integer',
    ];

    /**
     * Relacionamento polimórfico
     * Uma mídia pode estar vinculada a uma Page, User, Post, ou ficar solta (null)
     */
    public function mediaable()
    {
        return $this->morphTo();
    }

    /**
     * Scopes úteis para filtragem
     */
    public function scopeImages($query)
    {
        return $query->where('mime_type', 'like', 'image/%');
    }

    public function scopeDocuments($query)
    {
        return $query->where('mime_type', 'like', 'application/%');
    }

    /**
     * Acessores Globais
     */
    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->path);
    }

    /**
     * Acessor do Thumbnail (300x300)
     */
    public function getThumbUrlAttribute(): ?string
    {
        return $this->getUrlForVariant('thumb');
    }

    /**
     * Acessor em Alta Resolução para SEO e Redes Sociais (1200x630)
     */
    public function getLargeUrlAttribute(): ?string
    {
        return $this->getUrlForVariant('large');
    }

    /**
     * Auxiliar interno que resolve qualquer variação em cache evitando duplicação
     */
    public function getUrlForVariant(string $suffix): string
    {
        // SVG não tem variação, retorna a própria imagem original
        if (str_starts_with($this->mime_type, 'image/svg')) {
            return $this->url;
        }

        $pathInfo = pathinfo($this->path);

        // Pega o formato padrão das configurações (ex: webp)
        $format = strtolower(config('settings.media_formats', 'webp'));

        $variantName = $pathInfo['filename'] . '_' . $suffix . '.' . $format;
        $variantPath = 'media/uploads/cache/' . $variantName;

        // Se o arquivo da variação existir fisicamente no disco, retorna ele
        if (Storage::disk('public')->exists($variantPath)) {
            return Storage::disk('public')->url($variantPath);
        }

        // Fallback seguro: se a variação física não existir (uploads antigos), retorna a original
        return $this->url;
    }

    public function getIsImageAttribute(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function getSizeFormattedAttribute(): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->size;
        $unit = 0;
        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }
        return round($size, 2) . ' ' . $units[$unit];
    }

    public function getMediaableLabelAttribute()
    {
        return $this->mediaable?->title
            ?? $this->mediaable?->name
            ?? 'Sem título';
    }

    /**
     * Verifica se esta mídia é thumbnail de algum Post
     */
    public function postThumbnail()
    {
        return $this->hasOne(\App\Models\Post::class, 'thumbnail_id');
    }

    /**
     * Verifica se esta mídia é thumbnail de alguma Page
     */
    public function pageThumbnail()
    {
        return $this->hasOne(\App\Models\Page::class, 'thumbnail_id');
    }

    /**
     * Retorna info da publicação que usa esta mídia como thumbnail
     */
    public function getThumbnailOfAttribute()
    {
        // Verifica se é thumbnail de Post
        if ($this->postThumbnail) {
            return [
                'type'  => 'Post',
                'title' => $this->postThumbnail->title ?? 'Sem título',
                'url'   => route('admin.posts.edit', $this->postThumbnail->id),
            ];
        }

        // Verifica se é thumbnail de Page
        if ($this->pageThumbnail) {
            return [
                'type'  => 'Page',
                'title' => $this->pageThumbnail->title ?? 'Sem título',
                'url'   => route('admin.pages.edit', $this->pageThumbnail->id),
            ];
        }

        return null;
    }

    /**
     * Deleção segura: remove o arquivo físico e todos os seus caches ao deletar o registro
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function (Media $media) {
            // 1. Remove a imagem original física
            if (Storage::disk('public')->exists($media->path)) {
                Storage::disk('public')->delete($media->path);
            }

            // 2. Limpa as variações associadas em cache (_thumb, _large) de forma segura
            if (function_exists('deleteMediaVariants')) {
                $pathParts = explode('/', $media->path);
                $folder = $pathParts[1] ?? 'uploads';
                deleteMediaVariants($media->path, $folder);
            }
        });
    }
}
