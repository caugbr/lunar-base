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
        // ⚠️ mediaable_id e mediaable_type são definidos via relacionamento, não mass assignment
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
     * Acessores
     */
    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->path);
    }

    // public function getThumbUrlAttribute(): ?string
    // {
    //     // SVG não tem thumbnail, retorna a própria imagem
    //     if (str_starts_with($this->mime_type, 'image/svg')) {
    //         return $this->url;
    //     }

    //     $pathInfo = pathinfo($this->path);

    //     $thumbName = $pathInfo['filename'] . '_thumb.' . $pathInfo['extension'];
    //     $thumbPath = 'media/uploads/cache/' . $thumbName;

    //     \Log::info("PATH INFO", [
    //         "thumbPath" => $thumbPath,
    //         "exists" => Storage::disk('public')->exists($thumbPath),
    //         "url" => Storage::disk('public')->url($thumbPath)
    //     ]);

    //     // Verifica se o thumb existe, senão retorna a original
    //     if (Storage::disk('public')->exists($thumbPath)) {
    //         return Storage::disk('public')->url($thumbPath);
    //     }

    //     // fallback para original
    //     return $this->url;
    // }
    public function getThumbUrlAttribute(): ?string
    {
        // SVG não tem thumbnail, retorna a própria imagem
        if (str_starts_with($this->mime_type, 'image/svg')) {
            return $this->url;
        }

        $pathInfo = pathinfo($this->path);

        // 💡 CORREÇÃO: Pega o formato das configurações ou define 'webp' como padrão
        // Igualzinho está no seu ImageProcessor.php
        $format = strtolower(config('settings.media_formats', 'webp'));

        // Se você não tiver esse config global, pode usar apenas 'webp' diretamente:
        // $format = 'webp';

        $thumbName = $pathInfo['filename'] . '_thumb.' . $format;
        $thumbPath = 'media/uploads/cache/' . $thumbName;

        \Log::info("PATH INFO CORRIGIDO", [
            "thumbPath" => $thumbPath,
            "exists" => Storage::disk('public')->exists($thumbPath),
            "url" => Storage::disk('public')->url($thumbPath)
        ]);

        // Verifica se o thumb existe, senão retorna a original
        if (Storage::disk('public')->exists($thumbPath)) {
            return Storage::disk('public')->url($thumbPath);
        }

        // fallback para original
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

    /**
     * Deleção segura: remove o arquivo físico ao deletar o registro
     * ⚠️ Futuramente, adapte aqui para também remover thumbnails/derivados
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function (Media $media) {
            if (Storage::disk('public')->exists($media->path)) {
                Storage::disk('public')->delete($media->path);
            }
        });
    }
}
