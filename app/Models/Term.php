<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Term extends Model
{
    use HasFactory;

    protected $fillable = [
        'taxonomy_id',
        'name',
        'slug',
        'description',
        'parent_id',
        'order',
    ];

    /**
     * Relacionamento com a taxonomia
     */
    public function taxonomy()
    {
        return $this->belongsTo(Taxonomy::class);
    }

    /**
     * Relacionamento com o termo pai (para taxonomias hierárquicas)
     */
    public function parent()
    {
        return $this->belongsTo(Term::class, 'parent_id');
    }

    /**
     * Relacionamento com os termos filhos
     */
    public function children()
    {
        return $this->hasMany(Term::class, 'parent_id');
    }

    /**
     * Relacionamento polimórfico com páginas
     */
    public function pages()
    {
        return $this->morphedByMany(Page::class, 'termable', 'term_relationships');
    }

    /**
     * Relacionamento polimórfico com posts
     */
    public function posts()
    {
        return $this->morphedByMany(Post::class, 'termable', 'term_relationships');
    }

    /**
     * Conta quantas entidades (de qualquer tipo) estão associadas a este termo
     */
    public function relationshipsCount(): int
    {
        return \DB::table('term_relationships')
            ->where('term_id', $this->id)
            ->count();
    }

    /**
     * Verifica se o termo está em uso em qualquer entidade
     */
    public function isInUse(): bool
    {
        return $this->relationshipsCount() > 0;
    }
}
