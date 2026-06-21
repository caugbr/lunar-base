<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pages';

    protected $fillable = [
        'title',
        'slug',
        'content',
        'excerpt',
        'namespace',
        'is_main',
        'author_id',
        'status',
        'template',
        'thumbnail_id'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'is_main' => 'boolean'
    ];

    // ==========================================
    // RELACIONAMENTOS
    // ==========================================

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    // Thumbnail
    public function thumbnail()
    {
        return $this->belongsTo(Media::class, 'thumbnail_id');
    }

    // Galeria (Polimórfico)
    public function images()
    {
        return $this->morphMany(Media::class, 'mediaable');
    }

    /**
     * Relacionamento com os termos (taxonomias)
     */
    public function terms()
    {
        return $this->morphToMany(Term::class, 'termable', 'term_relationships');
    }

    // ==========================================
    // SCOPES
    // ==========================================

    /**
     * Filtra apenas páginas publicadas
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Filtra apenas páginas rascunho
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    // ==========================================
    // ACESSORS
    // ==========================================

    /**
     * URL pública e dinâmica da Página
     */
    public function getUrlAttribute(): string
    {
        $base = setting('permalinks.pages_base', '');
        $base = $base ? "/{$base}" : "";

        if ($this->namespace) {
            return url('/' . $this->namespace . '/' . $this->slug);
        }

        return url('/' . $this->slug);
    }

    public function getAuthorNameAttribute()
    {
        return $this->author->name;
    }

    /**
     * Retorna o status formatado
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'published' => 'Publicado',
            'draft' => 'Rascunho',
            'archived' => 'Arquivado',
            default => ucfirst($this->status),
        };
    }

    /**
     * Retorna a badge CSS para o status
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'published' => 'admin-badge admin-badge-active',
            'draft' => 'admin-badge admin-badge-trial',
            'archived' => 'admin-badge admin-badge-suspended',
            default => 'admin-badge',
        };
    }

    /**
     * Busca páginas por termo (slug da taxonomia + slug do termo)
     */
    public static function findByTerm($taxonomySlug, $termSlug)
    {
        $term = Term::whereHas('taxonomy', function($q) use ($taxonomySlug) {
            $q->where('slug', $taxonomySlug);
        })->where('slug', $termSlug)->first();

        if (!$term) {
            return collect();
        }

        return $term->pages()->where('status', 'published')->get();
    }
}
