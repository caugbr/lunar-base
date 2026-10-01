<?php

/* =========================================================================
 * Funções Globais para Actions
 * ========================================================================= */

if (!function_exists('addAction')) {
    function addAction(string $tag, callable $callback, int $priority = 10): void
    {
        app('php_hooks')->addAction($tag, $callback, $priority);
    }
}

if (!function_exists('doAction')) {
    function doAction(string $tag, mixed ...$args): void
    {
        app('php_hooks')->doAction($tag, ...$args);
    }
}

if (!function_exists('has_action')) {
    function has_action(string $tag): bool
    {
        return app('php_hooks')->hasAction($tag);
    }
}

/* =========================================================================
 * Funções Globais para Filters
 * ========================================================================= */

if (!function_exists('addFilter')) {
    function addFilter(string $tag, callable $callback, int $priority = 10): void
    {
        app('php_hooks')->addFilter($tag, $callback, $priority);
    }
}

if (!function_exists('aplyFilters')) {
    function aplyFilters(string $tag, mixed $value, mixed ...$args): mixed
    {
        return app('php_hooks')->applyFilters($tag, $value, ...$args);
    }
}

if (!function_exists('has_filter')) {
    function has_filter(string $tag): bool
    {
        return app('php_hooks')->hasFilter($tag);
    }
}
