<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class HookDiscoverer
{
    protected static string $cacheKey = 'lunar_discovered_hooks';

    /**
     * Limpa o cache físico da varredura de ganchos (Útil na ativação de temas/plugins)
     */
    public static function clearCache(): void
    {
        Cache::forget(self::$cacheKey);
    }

    /**
     * Retorna a lista de todos os hooks descobertos em disco organizados
     */
    public static function all(string $sector = 'all', bool $forceRefresh = false): array
    {
        if ($forceRefresh) {
            self::clearCache();
        }

        // Cache persistente para velocidade de carregamento instantânea
        $allHooks = Cache::rememberForever(self::$cacheKey, function () {
            return self::scanDirectories();
        });

        if ($sector === 'all' || empty($sector)) {
            return $allHooks;
        }

        // Filtra por setor específico (system, plugin ou theme)
        return array_filter($allHooks, function ($hook) use ($sector) {
            return $hook['sector'] === $sector;
        });
    }

    /**
     * Varre as pastas de views do sistema de forma recursiva
     */
    protected static function scanDirectories(): array
    {
        $directories = [
            'system' => base_path('resources/views'),
            'plugin' => base_path('plugins'),
            'theme'  => base_path('themes'),
        ];

        $hooks = [];

        foreach ($directories as $sector => $path) {
            if (!File::isDirectory($path)) {
                continue;
            }

            $directoryIterator = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS);
            $iterator = new RecursiveIteratorIterator($directoryIterator);

            foreach ($iterator as $file) {
                // Filtra apenas arquivos com extensão final .blade.php
                if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
                    self::scanFile($file->getPathname(), $sector, $hooks);
                }
            }
        }

        return array_values($hooks);
    }

    /**
     * Analisa o código do arquivo Blade usando Busca Dupla estável por Regex
     */
    protected static function scanFile(string $filePath, string $sector, array &$hooks): void
    {
        $content = file_get_contents($filePath);

        // 💡 VIA 1: Busca ganchos fechados com tags </x-hook> (Podem ser Filtros ou Ações dependendo do miolo)
        preg_match_all('/<x-hook\s+([^>]*?)>(.*?)<\/x-hook>/is', $content, $matchesClosed, PREG_SET_ORDER);

        foreach ($matchesClosed as $match) {
            $attributesString = $match[1];
            $innerContent = $match[2];

            self::parseAndRegisterHook($attributesString, $innerContent, $sector, $filePath, $hooks);
        }

        // 💡 VIA 2: Busca ganchos auto-fechados do tipo <x-hook ... /> (Sempre são Ações)
        preg_match_all('/<x-hook\s+([^>]*?)\/>/is', $content, $matchesSelf, PREG_SET_ORDER);

        foreach ($matchesSelf as $match) {
            $attributesString = $match[1];

            self::parseAndRegisterHook($attributesString, '', $sector, $filePath, $hooks);
        }
    }

    /**
     * Analisa as strings de atributos e monta o mapa final do hook
     */
    protected static function parseAndRegisterHook(string $attributesString, string $innerContent, string $sector, string $filePath, array &$hooks): void
    {
        // Normaliza os atributos removendo quebras de linha
        $attributesStringNormalized = str_replace(["\r", "\n"], ' ', $attributesString);

        // Captura os pares de chave="valor" (ex: name="menu" ou :params="['id' => 1]")
        preg_match_all('/([\w:]+)\s*=\s*["\']([^"\']*)["\']/i', $attributesStringNormalized, $attrMatches, PREG_SET_ORDER);

        $attrs = [];
        foreach ($attrMatches as $match) {
            $key = strtolower($match[1]);
            $attrs[$key] = $match[2];
        }

        // Lê o nome do hook (suporta com ou sem binding de dois pontos do Blade)
        $name = $attrs['name'] ?? $attrs[':name'] ?? null;

        if ($name) {
            $name = trim($name, "'\"");

            $desc   = $attrs['desc'] ?? $attrs['description'] ?? null;
            $params = $attrs['params'] ?? $attrs[':params'] ?? null;

            // 💡 CORREÇÃO: Usamos apenas empty(trim()) para preservar tags HTML de ícones como conteúdo válido!
            $type = empty(trim($innerContent)) ? 'action' : 'filter';

            // Evita duplicidade se o mesmo gancho for declarado em arquivos diferentes
            if (!isset($hooks[$name])) {
                $hooks[$name] = [
                    'name'   => $name,
                    'desc'   => $desc ? trim($desc, "'\"") : null,
                    'params' => $params ? trim($params, "'\"") : null,
                    'type'   => $type,
                    'sector' => $sector,
                    'file'   => str_replace(base_path(), '', $filePath),
                ];
            } elseif ($type === 'filter') {
                // Se já estiver cadastrado como 'action' por um arquivo auto-fechado,
                // mas outro arquivo declarar como 'filter', prioriza o tipo com conteúdo de fallback
                $hooks[$name]['type'] = 'filter';
            }
        }
    }

    /**
     * Renderiza o dropdown <select> HTML agrupado por optgroups e customizável
     */
    public static function renderSelect(array $options = []): string
    {
        $name        = $options['name'] ?? 'hook_name';
        $id          = $options['id'] ?? $name;
        $placeholder = $options['placeholder'] ?? '-- Selecione um ponto de exibição --';
        $selected    = $options['selected'] ?? null;
        $sector      = $options['sector'] ?? 'all';
        $class       = $options['class'] ?? 'form-input';

        $hooks = self::all($sector);

        // Agrupa os ganchos por setor para criar os blocos visuais de agrupamento
        $grouped = [];
        foreach ($hooks as $hook) {
            $grouped[$hook['sector']][] = $hook;
        }

        // Títulos amigáveis para as categorias no painel do administrador
        $sectorLabels = [
            'theme'  => 'Tema Ativo (Recomendado)',
            'plugin' => 'Plugins Instalados',
            'system' => 'Sistema / Core',
        ];

        $html = '<select name="' . e($name) . '" id="' . e($id) . '" class="' . e($class) . '">';

        if ($placeholder !== false && $placeholder !== '') {
            $html .= '<option value="">' . e($placeholder) . '</option>';
        }

        // Sequência de exibição preferencial dos blocos (Tema -> Plugins -> Core)
        $order = ['theme', 'plugin', 'system'];
        foreach ($order as $secKey) {
            if (empty($grouped[$secKey])) {
                continue;
            }

            $html .= '<optgroup label="' . e($sectorLabels[$secKey]) . '">';
            foreach ($grouped[$secKey] as $hook) {
                // Compara o nome do gancho para marcar como selecionado (Selected)
                $isSel = ($selected === $hook['name']) ? ' selected' : '';

                // Identificador visual: Mostra se é um Filtro ou uma Ação baseado na presença de slot
                $badge = ($hook['type'] ?? 'action') === 'filter' ? '[filtro] ' : '[ação] ';

                // Formata o rótulo: Exibe a descrição com o tipo e nome técnico entre parênteses
                $label = $hook['desc']
                    ? $badge . e($hook['name']) . ' (' . e($hook['desc']) . ')'
                    : $badge . e($hook['name']);

                // Guarda as informações de parâmetros no atributo title para exibir uma dica de tela
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
