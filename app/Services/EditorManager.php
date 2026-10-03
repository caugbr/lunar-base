<?php

namespace App\Services;

class EditorManager
{
    /**
     * Lista de scripts JS adicionados por Temas e Plugins
     */
    protected static array $scripts = [];

    /**
     * Lista de estilos CSS adicionados por Temas e Plugins
     */
    protected static array $styles = [];

    /**
     * Registra um script JS externo para o Editor
     */
    public static function registerScript(string $src): void
    {
        if (!in_array($src, static::$scripts)) {
            static::$scripts[] = $src;
        }
    }

    /**
     * Registra um CSS externo para o Editor
     */
    public static function registerStyle(string $src): void
    {
        if (!in_array($src, static::$styles)) {
            static::$styles[] = $src;
        }
    }

    /**
     * Retorna todos os scripts registrados
     */
    public static function getScripts(): array
    {
        return static::$scripts;
    }

    /**
     * Retorna todos os estilos registrados
     */
    public static function getStyles(): array
    {
        return static::$styles;
    }

    /**
     * Retorna um mapa [ 'tipoDoBloco' => ['plugin_name' => '...', 'folder_name' => '...'] ]
     * varrendo os manifestos lidos por allPluginsManifests().
     *
     * @return array
     */
    public static function editorBlocksMap(): array
    {
        static $map = null;

        if ($map !== null) {
            return $map;
        }

        $map = [];
        $allManifests = allPluginsManifests();

        foreach ($allManifests as $folderName => $manifest) {
            $blocks = $manifest['editorBlocks'] ?? $manifest['insertedBlocks'] ?? [];

            foreach ($blocks as $blockType) {
                $map[$blockType] = [
                    'plugin_name'  => $manifest['name'] ?? $folderName,
                    'folder_name'  => $folderName,
                    'activate_url' => route('admin.plugins.index', ['search' => $manifest['name'] ?? $folderName]),
                ];
            }
        }

        return $map;
    }
}
