<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'posts';

    protected $fillable = [
        'title',
        'slug',
        'content',
        'excerpt',
        'author_id',
        'status',
        'template',
        'featured',
        'sticky',
        'published_at',
        'thumbnail_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'published_at' => 'datetime',
        'featured' => 'boolean',
        'sticky' => 'boolean',
    ];

    // ==========================================
    // RELACIONAMENTOS
    // ==========================================

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function thumbnail()
    {
        return $this->belongsTo(Media::class, 'thumbnail_id');
    }

    // Galeria polimórfica (mesmo padrão das páginas)
    public function images()
    {
        return $this->morphMany(Media::class, 'mediaable');
    }

    // Taxonomias polimórficas (mesmo padrão das páginas)
    public function terms()
    {
        return $this->morphToMany(Term::class, 'termable', 'term_relationships');
    }

    // ==========================================
    // SCOPES
    // ==========================================

    /**
     * Apenas publicados E com published_at no passado (ou agora)
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                     ->where(function ($q) {
                         $q->whereNull('published_at')
                           ->orWhere('published_at', '<=', now());
                     });
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'published')
                     ->where('published_at', '>', now());
    }

    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    public function scopeSticky($query)
    {
        return $query->where('sticky', true);
    }

    /**
     * Ordenação padrão do feed: sticky primeiro, depois published_at desc
     */
    public function scopeFeedOrder($query)
    {
        return $query->orderBy('sticky', 'desc')
                     ->orderBy('published_at', 'desc');
    }

    /**
     * Filtra posts por slug de taxonomia + slug de termo
     * Ex: Post::byTerm('categories', 'laravel')
     */
    public function scopeByTerm($query, string $taxonomySlug, string $termSlug)
    {
        return $query->whereHas('terms', function ($q) use ($taxonomySlug, $termSlug) {
            $q->where('slug', $termSlug)
              ->whereHas('taxonomy', function ($tq) use ($taxonomySlug) {
                  $tq->where('slug', $taxonomySlug);
              });
        });
    }

    // ==========================================
    // ACESSORS
    // ==========================================

/**
     * URL pública do Post individual: ex: /post/meu-post (ou /blog/meu-post)
     */
    public function getUrlAttribute(): string
    {
        $postBase = setting('permalinks.posts_base', 'post');
        return url("/{$postBase}/" . $this->slug);
    }

    /**
     * URL de listagem por termo: ex: /blog/categoria/laravel
     */
    public function getTermUrlAttribute(): ?string
    {
        $blogBase = setting('permalinks.blog_base', 'blog');

        $firstTerm = $this->terms->first();
        if (!$firstTerm || !$firstTerm->taxonomy) {
            return null;
        }

        return url("/{$blogBase}/" . $firstTerm->taxonomy->slug . '/' . $firstTerm->slug);
    }

    /**
     * Status formatado
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'published' => $this->published_at && $this->published_at->isFuture()
                ? 'Agendado'
                : 'Publicado',
            'draft' => 'Rascunho',
            'archived' => 'Arquivado',
            default => ucfirst($this->status),
        };
    }

    /**
     * Badge CSS para status
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'published' => $this->published_at && $this->published_at->isFuture()
                ? 'admin-badge admin-badge-trial'   // amarelo = agendado
                : 'admin-badge admin-badge-active', // verde = publicado
            'draft' => 'admin-badge admin-badge-trial',
            'archived' => 'admin-badge admin-badge-suspended',
            default => 'admin-badge',
        };
    }

    /**
     * Nome do autor (igual ao Page)
     */
    public function getAuthorNameAttribute(): ?string
    {
        return $this->author?->name;
    }

    /**
     * Tempo estimado de leitura (em minutos)
     */
    public function getReadingTimeAttribute(): int
    {
        $wordCount = str_word_count(strip_tags($this->content));
        return max(1, ceil($wordCount / 200)); // 200 palavras/min
    }

    /**
     * Excerpt: usa o campo ou gera dos primeiros 160 chars do content
     */
    public function getExcerptAttribute(?string $value): string
    {
        if ($value) {
            return $value;
        }
        return \Str::limit(strip_tags($this->content), 160);
    }

    /**
     * Data de publicação formatada
     */
    public function getPublishedAtFormattedAttribute(): string
    {
        if (!$this->published_at) {
            return $this->created_at->format('d/m/Y H:i');
        }
        return $this->published_at->format('d/m/Y H:i');
    }
}
