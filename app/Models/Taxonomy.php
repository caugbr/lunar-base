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
    ];

    protected $casts = [
        'hierarchical' => 'boolean',
    ];

    /**
     * Relacionamento com os termos
     */
    public function terms()
    {
        return $this->hasMany(Term::class);
    }

    /**
     * Busca uma taxonomia pelo slug
     */
    public static function findBySlug($slug)
    {
        return static::where('slug', $slug)->first();
    }
}
