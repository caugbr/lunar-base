<?php

namespace App\Support;

class HookManager
{
    protected static array $hooks = [];

    /**
     * @param string $hook O nome do ponto de injeção
     * @param callable $callback A função de renderização
     * @param string $origin Identificador (ex: nome do plugin) para debug
     */
    public static function register(string $hook, callable $callback, string $origin = 'Core'): void
    {
        self::$hooks[$hook][] = [
            'callback' => $callback,
            'origin'   => $origin
        ];
    }

    public static function render(string $hook, array $params = []): string
    {
        if (!isset(self::$hooks[$hook]) || empty(self::$hooks[$hook])) {
            return '';
        }

        ob_start();

        foreach (self::$hooks[$hook] as $item) {
            if (config('app.debug')) {
                echo "<!-- Hook: {$hook} | Source: {$item['origin']} | start -->";
            }

            echo call_user_func($item['callback'], $params);

            if (config('app.debug')) {
                echo "<!-- Hook: {$hook} | Source: {$item['origin']} | end -->";
            }
        }

        return ob_get_clean();
    }
}
