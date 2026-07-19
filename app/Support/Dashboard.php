<?php

namespace App\Support;

class Dashboard
{
    protected static array $boxes = [];

    public static function add(string $id, array $config): void
    {
        static::$boxes[$id] = array_merge([
            'id'         => $id,
            'span'       => 1,
            'priority'   => 100,
            'permission' => null,
            'icon'       => null,
        ], $config);
    }

    public static function remove(string $id): void
    {
        unset(static::$boxes[$id]);
    }

    public static function modify(string $id, callable $callback): void
    {
        if (!isset(static::$boxes[$id])) {
            return;
        }

        // Passa por referência para o callback modificar direto
        $callback(static::$boxes[$id]);
    }

    public static function getAll(): array
    {
        return array_values(static::$boxes);
    }

    public static function get(string $id): ?array
    {
        return static::$boxes[$id] ?? null;
    }

    public static function has(string $id): bool
    {
        return isset(static::$boxes[$id]);
    }

    public static function render(string $id, array $params = []): string
    {
        $box = static::$boxes[$id] ?? null;

        if (!$box) {
            return '';
        }

        // Verifica permissão
        if (!empty($box['permission']) && !auth()->user()?->permission($box['permission'])) {
            return '';
        }

        // Valida formato do controller
        if (empty($box['controller']) || !str_contains($box['controller'], '@')) {
            return '';
        }

        try {
            // Chama o controller
            [$class, $method] = explode('@', $box['controller']);
            $controller = app($class);
            $result = $controller->$method(...$params);

            // Garante que o resultado seja string HTML
            $content = $result instanceof \Illuminate\View\View
                ? $result->render()
                : (string) $result;

            // Renderiza na moldura
            return view('admin.dashboard.box', [
                'id'      => $box['id'],
                'title'   => $box['title'] ?? '',
                'icon'    => $box['icon'] ?? null,
                'span'    => $box['span'] ?? 1,
                'content' => $content,
            ])->render();

        } catch (\Throwable $e) {
            // Em desenvolvimento, mostra o erro; em produção, retorna vazio
            if (app()->hasDebugModeEnabled()) {
                return "<div class='dashboard-box-error'>Erro no box '{$id}': {$e->getMessage()}</div>";
            }
            return '';
        }
    }

    public static function clear(): void
    {
        static::$boxes = [];
    }
}
