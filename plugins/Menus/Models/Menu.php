<?php

namespace Plugins\Menus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    protected $table = 'menus';
    protected $fillable = ['name', 'slug', 'hook'];

    /**
     * Retorna todos os itens vinculados a este menu
     */
    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'menu_id');
    }

    /**
     * Retorna apenas as raízes do menu (itens de nível 0, sem pai) ordenados
     */
    public function rootItems(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'menu_id')
            ->whereNull('parent_id')
            ->orderBy('order', 'asc');
    }
}
