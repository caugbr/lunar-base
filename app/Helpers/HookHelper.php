<?php

use App\Support\HookDiscoverer;

if (!function_exists('hook')) {
    function hook(string $hook, array $params = []) {
        return \App\Support\HookManager::render($hook, $params);
    }
}

if (!function_exists('get_discovered_hooks')) {
    /**
     * Retorna a lista de todos os hooks descobertos em disco
     */
    function get_discovered_hooks(string $sector = 'all', bool $force = false): array
    {
        return HookDiscoverer::all($sector, $force);
    }
}

if (!function_exists('render_hooks_select')) {
    /**
     * Retorna a tag <select> HTML populada com os ganchos do sistema
     */
    function render_hooks_select(array $options = []): string
    {
        return HookDiscoverer::renderSelect($options);
    }
}
