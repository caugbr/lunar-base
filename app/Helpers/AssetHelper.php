<?php

use App\Services\AssetManager;

if (!function_exists('add_style')) {
    function add_style(string $handle, string $src, array $deps = [], string $version = '1.0.0', string $media = 'all'): void
    {
        app(AssetManager::class)->addStyle($handle, $src, $deps, $version, $media);
    }
}

if (!function_exists('add_script')) {
    function add_script(string $handle, string $src, array $deps = [], string $version = '1.0.0', bool $inFooter = true, bool $defer = false): void
    {
        app(AssetManager::class)->addScript($handle, $src, $deps, $version, $inFooter, $defer);
    }
}

if (!function_exists('add_inline_style')) {
    /**
     * Adiciona CSS inline (ex: cores dinâmicas, variáveis de tema)
     */
    function add_inline_style(string $css): void
    {
        app(AssetManager::class)->addInlineStyle($css);
    }
}

if (!function_exists('add_inline_script')) {
    /**
     * Adiciona JavaScript inline (ex: variáveis de backend, configurações dinâmicas)
     */
    function add_inline_script(string $js): void
    {
        app(AssetManager::class)->addInlineScript($js);
    }
}
