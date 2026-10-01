<?php

use App\Support\HookDiscoverer;

if (!function_exists('hook')) {
    function hook(string $hook, array $params = []) {
        return \App\Support\HookManager::render($hook, $params);
    }
}

if (!function_exists('getDiscoveredHooks')) {
    /**
     * Retorna a lista de todos os hooks descobertos em disco
     */
    function getDiscoveredHooks(string $sector = 'all', bool $force = false): array
    {
        return HookDiscoverer::all($sector, $force);
    }
}

if (!function_exists('renderHooksSelect')) {
    /**
     * Retorna a tag <select> HTML populada com os ganchos do sistema
     */
    function renderHooksSelect(array $options = []): string
    {
        return HookDiscoverer::renderSelect($options);
    }
}
