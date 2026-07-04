<?php

if (!function_exists('hook')) {
    function hook(string $hook, array $params = []) {
        return \App\Support\HookManager::render($hook, $params);
    }
}
