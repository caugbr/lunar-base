<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Taxonomy extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'hierarchical',
        'unique',
        'target_types',
    ];

    protected $casts = [
        'hierarchical' => 'boolean',
        'unique' => 'boolean',
        'target_types' => 'array',
    ];

    /**
     * Relacionamento com os termos
     */
    public function terms()
    {
        return $this->hasMany(Term::class)
            ->orderBy('order', 'asc')
            ->orderBy('name', 'asc');
    }

    /**
     * Busca uma taxonomia pelo slug
     */
    public static function findBySlug($slug)
    {
        return static::where('slug', $slug)->first();
    }

    /**
     * Escopo para buscar apenas taxonomias aplicáveis a um tipo específico (ex: 'post' ou 'page')
     */
    public function scopeForType($query, string $type)
    {
        return $query->where(function ($q) use ($type) {
            $q->whereNull('target_types')
            ->orWhereJsonContains('target_types', $type);
        });
    }
}
