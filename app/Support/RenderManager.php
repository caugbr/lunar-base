<?php

namespace App\Support;

class RenderManager
{
    protected static array $renders = [];

    /**
     * Registra um despachante de renderização de bloco.
     *
     * @param string $name Nome do bloco (ex: 'featured_posts', 'latest_posts')
     * @param callable $callback Função que busca os dados e retorna o HTML
     */
    public static function register(string $name, callable $callback): void
    {
        self::$renders[$name] = $callback;
    }

    /**
     * Executa a renderização do bloco.
     */
    public static function render(string $name, array $params = []): string
    {
        if (!isset(self::$renders[$name])) {
            return '';
        }

        return call_user_func(self::$renders[$name], $params) ?? '';
    }

    public static function has(string $name): bool
    {
        return isset(self::$renders[$name]);
    }
}
