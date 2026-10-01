<?php

use App\Models\Plugin;
use App\Models\Theme;
use Illuminate\Support\Facades\Schema;

if (! function_exists('activePlugins')) {
    /**
     * Retorna a lista com os nomes das pastas de todos os plugins ativos.
     *
     * @return array<string>
     */
    function activePlugins(): array
    {
        static $pluginsList = null;

        if ($pluginsList === null) {
            try {
                if (Schema::hasTable('plugins')) {
                    $pluginsList = Plugin::where('is_active', true)
                        ->pluck('folder_name')
                        ->toArray();
                } else {
                    $pluginsList = [];
                }
            } catch (\Throwable $e) {
                $pluginsList = [];
            }
        }

        return $pluginsList;
    }
}

if (! function_exists('activeTheme')) {
    /**
     * Retorna o nome da pasta do tema ativo atual (ou null se nenhum ativo).
     *
     * @return string|null
     */
    function activeTheme(): ?string
    {
        static $themeFolder = false;

        if ($themeFolder === false) {
            try {
                if (Schema::hasTable('themes')) {
                    $theme = Theme::where('is_active', true)->first();
                    $themeFolder = $theme ? $theme->folder_name : null;
                } else {
                    $themeFolder = null;
                }
            } catch (\Throwable $e) {
                $themeFolder = null;
            }
        }

        return $themeFolder;
    }
}

if (! function_exists('isPluginActive')) {
    /**
     * Verifica se um plugin está ativo pelo nome da sua pasta.
     *
     * @param string $folderName
     * @return bool
     */
    function isPluginActive(string $folderName): bool
    {
        static $activePluginsMap = null;

        if ($activePluginsMap === null) {
            // Reutiliza activePlugins() e usa array_flip para checagem instantânea (O(1))
            $activePluginsMap = array_flip(activePlugins());
        }

        return isset($activePluginsMap[$folderName]);
    }
}

if (! function_exists('isThemeActive')) {
    /**
     * Verifica se um tema está ativo pelo nome da sua pasta.
     *
     * @param string $folderName
     * @return bool
     */
    function isThemeActive(string $folderName): bool
    {
        return activeTheme() === $folderName;
    }
}

if (! function_exists('isAddonActive')) {
    /**
     * Verifica se um addon (tema ou plugin) está ativo pelo nome da sua pasta.
     *
     * @param string $folderName
     * @return bool
     */
    function isAddonActive(string $folderName): bool
    {
        return isPluginActive($folderName) || isThemeActive($folderName);
    }
}

if (! function_exists('addonInfo')) {
    /**
     * Identifica o tipo (plugin|theme) e a pasta a partir de um caminho de arquivo ou URL.
     *
     * @param string $pathOrUrl
     * @param string $returnType 'array' | 'object'
     * @return array|object|null
     */
    function addonInfo(string $pathOrUrl, string $returnType = 'array')
    {
        $normalizedPath = str_replace('\\', '/', $pathOrUrl);

        if (preg_match('#(?:^|/)(plugins|themes)/([^/]+)#i', $normalizedPath, $matches)) {
            $type = rtrim(strtolower($matches[1]), 's');
            $folderName = $matches[2];

            $ret = [
                'type'        => $type,
                'folder_name' => $folderName,
            ];

            return $returnType === 'object' ? (object) $ret : $ret;
        }

        $empty = [
            'type'        => null,
            'folder_name' => null,
        ];

        return $returnType === 'object' ? (object) $empty : $empty;
    }
}
