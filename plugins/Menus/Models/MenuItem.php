<?php

namespace Plugins\Menus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MenuItem extends Model
{
    protected $table = 'menu_items';

    protected $fillable = [
        'menu_id', 'parent_id', 'label', 'type', 'url',
        'model_type', 'model_id', 'order', 'target', 'class'
    ];

    /**
     * Pertence a um Menu pai
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    /**
     * Pertence a um Item pai (caso seja sub-menu)
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    /**
     * Possui muitas sub-entradas filhas ordenadas
     */
    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')
            ->orderBy('order', 'asc');
    }

    /**
     * Relacionamento polimórfico flexível com Page, Post ou Term
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * RESOLVEDOR DINÂMICO DE LINKS:
     * Retorna a URL final de forma reativa, sem risco de links quebrados
     */
    public function getUrlAttribute(): string
    {
        if ($this->type === 'custom') {
            return $this->url ?? '#';
        }

        // Se estiver vinculado a uma Page, Post ou Term, resolve a URL diretamente do model
        if ($this->model) {
            return $this->model->url;
        }

        return '#';
    }

    /**
     * RESOLVEDOR DINÂMICO DE RÓTULO:
     * Se o administrador não digitar um rótulo personalizado, ele busca o título original do modelo
     */
    public function getLabelAttribute(?string $value): string
    {
        if ($value) {
            return $value;
        }

        if ($this->model) {
            return $this->model->title ?? $this->model->name ?? '';
        }

        return '';
    }
}
