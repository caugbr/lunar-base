<?php

use App\Services\AssetManager;

if (!function_exists('addStyle')) {
    function addStyle(string $handle, string $src, array $deps = [], string $version = '1.0.0', string $media = 'all'): void
    {
        app(AssetManager::class)->addStyle($handle, $src, $deps, $version, $media);
    }
}

if (!function_exists('addScript')) {
    function addScript(string $handle, string $src, array $deps = [], string $version = '1.0.0', bool $inFooter = true, bool $defer = false): void
    {
        app(AssetManager::class)->addScript($handle, $src, $deps, $version, $inFooter, $defer);
    }
}

if (!function_exists('addInlineStyle')) {
    /**
     * Adiciona CSS inline (ex: cores dinâmicas, variáveis de tema)
     */
    function addInlineStyle(string $css): void
    {
        app(AssetManager::class)->addInlineStyle($css);
    }
}

if (!function_exists('addInlineScript')) {
    /**
     * Adiciona JavaScript inline (ex: variáveis de backend, configurações dinâmicas)
     */
    function addInlineScript(string $js): void
    {
        app(AssetManager::class)->addInlineScript($js);
    }
}
