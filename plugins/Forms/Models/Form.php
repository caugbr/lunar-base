<?php

namespace Plugins\Forms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Form extends Model
{
    use HasFactory;

    // Campos que podem ser preenchidos em massa (Mass Assignment)
    protected $fillable = [
        'slug',
        'title',
        'email_to',
        'fields_schema',
        'is_active',
        'submit_message',
        'submit_button_label'
    ];

    // A mágica: converte automaticamente o JSON do banco em Array PHP
    // e converte o Array PHP de volta em JSON na hora de salvar.
    protected $casts = [
        'fields_schema' => 'array',
        'is_active'     => 'boolean',
    ];

    /**
     * Relação: Um formulário tem muitas submissões (respostas).
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class, 'form_id');
    }

    /**
     * Scope: Facilita buscar apenas formulários ativos no controller.
     * Uso: Form::active()->where('slug', 'contato')->first();
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
