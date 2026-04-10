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
     * Relacionamento com as páginas
     */
    public function pages()
    {
        return $this->belongsToMany(Page::class, 'term_relationships');
    }
}
