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
}
