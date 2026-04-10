<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TermRelationship extends Model
{
    use HasFactory;

    protected $table = 'term_relationships';

    protected $fillable = [
        'term_id',
        'page_id',
    ];

    /**
     * Relacionamento com o termo
     */
    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    /**
     * Relacionamento com a página
     */
    public function page()
    {
        return $this->belongsTo(Page::class);
    }
}
