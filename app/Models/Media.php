<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'path', 'mime_type', 'size', 'width', 'height',
        'alt', 'caption', 'hash'
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
