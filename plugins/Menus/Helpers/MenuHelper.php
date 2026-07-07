<?php

use Plugins\Menus\Models\Menu;

if (!function_exists('renderMenu')) {
    /**
     * Renderiza um menu dinâmico diretamente a partir do seu slug
     */
    function renderMenu(string $slug): string
    {
        $menu = Menu::where('slug', $slug)->first();

        // Se o menu não existir ou estiver vazio, retorna string vazia de forma segura
        if (!$menu || $menu->rootItems->isEmpty()) {
            return '';
        }

        // Renderiza a estrutura raiz recursiva que já criamos
        if (view()->exists('menus::public.root')) {
            return view('menus::public.root', [
                'items' => $menu->rootItems
            ])->render();
        }

        return '';
    }
}
