<?php

namespace App\Support;

class DynamicRoutes
{
    protected static array $resolvers = [];

    /**
     * Plugin registra sua rota.
     *
     * @param string $method (GET, POST, PUT, DELETE)
     * @param string $path (ex: admin/forms/{id})
     * @param callable $resolver
     */
    public static function register(string $method, string $path, callable $resolver): void
    {
        $path = trim($path, '/');

        // Converte {slug} ou {id} em capture groups de regex
        $regex = '#^' . preg_replace('/\{[a-zA-Z0-9_]+\}/', '([^/]+)', $path) . '$#';

        self::$resolvers[] = [
            'method'   => strtoupper($method),
            'regex'    => $regex,
            'callback' => $resolver
        ];
    }

    /**
     * O Orquestrador chama isso para carregar a view
     */
    public static function resolve(string $url)
    {
        $url = trim($url, '/');
        $method = request()->method();

        foreach (self::$resolvers as $route) {
            if ($method === $route['method'] && preg_match($route['regex'], $url, $matches)) {
                array_shift($matches); // Remove o full match
                return call_user_func_array($route['callback'], $matches);
            }
        }
        return false;
    }
}
