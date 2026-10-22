<?php

namespace App\Support;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class HookDiscoverer
{
    protected static string $cachePath = 'bootstrap/cache/hooks.php';

    /**
     * Limpa o cache físico da varredura de ganchos e força nova geração
     */
    public static function clearCache(): void
    {
        $file = base_path(self::$cachePath);

        if (File::exists($file)) {
            File::delete($file);
        }

        Artisan::call('hooks:scan');
    }

    /**
     * Retorna os hooks de template (100% retrocompatível com chamadas anteriores)
     */
    public static function all(string $sector = 'all'): array
    {
        return self::templates($sector);
    }

    /**
     * Retorna apenas os hooks visuais de template (<x-hook>)
     */
    public static function templates(string $sector = 'all'): array
    {
        $data = self::loadCompiledData();
        $hooks = $data['templates'] ?? [];

        if ($sector === 'all' || empty($sector)) {
            return $hooks;
        }

        return array_values(array_filter($hooks, fn($hook) => ($hook['sector'] ?? '') === $sector));
    }

    /**
     * Retorna apenas os hooks de PHP (doAction / applyFilters)
     */
    public static function php(string $sector = 'all'): array
    {
        $data = self::loadCompiledData();
        $hooks = $data['php'] ?? [];

        if ($sector === 'all' || empty($sector)) {
            return $hooks;
        }

        return array_values(array_filter($hooks, fn($hook) => ($hook['sector'] ?? '') === $sector));
    }

    /**
     * Carrega o arquivo compilado com fallback automático
     */
    protected static function loadCompiledData(): array
    {
        $cacheFile = base_path(self::$cachePath);

        if (File::exists($cacheFile)) {
            return require $cacheFile;
        }

        // Se o arquivo não existir, roda a varredura na hora
        Artisan::call('hooks:scan');

        if (File::exists($cacheFile)) {
            return require $cacheFile;
        }

        return ['templates' => [], 'php' => []];
    }

    /**
     * Renderiza o dropdown <select> HTML agrupado por optgroups e customizável
     */
    public static function renderSelect(array $options = []): string
    {
        $name        = $options['name'] ?? 'hook_name';
        $id          = $options['id'] ?? $name;
        $placeholder = $options['placeholder'] ?? '-- Selecione --';
        $selected    = $options['selected'] ?? null;
        $sector      = $options['sector'] ?? 'all';
        $class       = $options['class'] ?? 'form-input';

        $hooks = self::templates($sector);

        $grouped = [];
        foreach ($hooks as $hook) {
            $grouped[$hook['sector']][] = $hook;
        }

        $sectorLabels = [
            'theme'  => 'Tema Ativo (Recomendado)',
            'plugin' => 'Plugins Instalados',
            'system' => 'Sistema / Core',
        ];

        $html = '<select name="' . e($name) . '" id="' . e($id) . '" class="' . e($class) . '">';

        if ($placeholder !== false && $placeholder !== '') {
            $html .= '<option value="">' . e($placeholder) . '</option>';
        }

        $order = ['theme', 'plugin', 'system'];
        foreach ($order as $secKey) {
            if (empty($grouped[$secKey])) {
                continue;
            }

            $html .= '<optgroup label="' . e($sectorLabels[$secKey]) . '">';
            foreach ($grouped[$secKey] as $hook) {
                $isSel = ($selected === $hook['name']) ? ' selected' : '';
                $badge = ($hook['type'] ?? 'action') === 'filter' ? '[filtro] ' : '[ação] ';

                $label = $hook['desc']
                    ? $badge . e($hook['name']) . ' (' . e($hook['desc']) . ')'
                    : $badge . e($hook['name']);

                $paramAttr = $hook['params']
                    ? ' data-params="' . e($hook['params']) . '" title="Parâmetros fornecidos: ' . e($hook['params']) . '"'
                    : '';

                $html .= '<option value="' . e($hook['name']) . '"' . $isSel . $paramAttr . '>' . $label . '</option>';
            }
            $html .= '</optgroup>';
        }

        $html .= '</select>';

        return $html;
    }
}
