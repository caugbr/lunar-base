<?php

/**
 * ContentHelper
 *
 * Orquestrador principal para o processamento de conteúdo das páginas.
 *
 * Responsabilidades:
 * 1. Aplicar correções globais no HTML (ex: tags de script/style comentadas para o TinyMCE).
 * 2. Delegar o parsing e renderização de shortcodes para o Trait `Shortcodes`.
 *
 * Nota: A lógica específica de renderização de cada shortcode reside dentro do Trait.
 * Este arquivo deve permanecer focado apenas na orquestração e correção do texto bruto.
 */

namespace App\Helpers;

use App\Traits\Shortcodes;
use Illuminate\Support\Str;

class ContentHelper
{
    use Shortcodes;

    public static function parseShortcodes($content)
    {
        // 1. Lógica do TinyMCE (mantida)
        $content = preg_replace_callback(
            '/<!--(script|style|link)([^>]*)>(.*?)<\/\1-->/is',
            function($matches) {
                return '<' . $matches[1] . $matches[2] . '>' . $matches[3] . '</' . $matches[1] . '>';
            },
            $content
        );

        // 2. Parser e Roteamento
        return self::parseAndRenderShortcodes($content);
    }

    private static function parseAndRenderShortcodes($content)
    {
        $pattern = '/\[([a-zA-Z0-9_\-]+)((?:\s+[a-zA-Z0-9_\-]+=(?:"[^"]*"|\'[^\']*\'))*)\s*\]/i';

        return preg_replace_callback($pattern, function($matches) {
            $tag = strtolower($matches[1]);
            $attrsString = $matches[2] ?? '';
            $attributes = self::parseAttributes($attrsString);

            return self::renderShortcode($tag, $attributes);
        }, $content);
    }

    private static function parseAttributes($attrsString)
    {
        $attributes = [];
        if (preg_match_all('/([a-zA-Z0-9_\-]+)=(?:"([^"]*)"|\'([^\']*)\')/', $attrsString, $attrMatches, PREG_SET_ORDER)) {
            foreach ($attrMatches as $attr) {
                $attributes[$attr[1]] = $attr[2] !== '' ? $attr[2] : ($attr[3] ?? '');
            }
        }
        return $attributes;
    }

    private static function renderShortcode($tag, $attributes)
    {
        $method = "render" . Str::studly($tag);

        if (method_exists(self::class, $method)) {
            return self::{$method}($attributes);
        }

        return "[{$tag}]"; // Fallback
    }
}
