<?php

namespace App\Helpers;

use App\Traits\Shortcodes;
use Illuminate\Support\Str;

class ContentHelper
{
    use Shortcodes;

    // Guarda os IDs de assets que já foram renderizados nesta requisição
    protected static $renderedAssets = [];

    /**
     * Verifica se um asset já foi carregado e o registra.
     * Retorna true se for a primeira vez (pode carregar).
     * Retorna false se for duplicado (bloquear).
     */
    public static function once($id)
    {
        if (in_array($id, self::$renderedAssets)) {
            return false;
        }

        self::$renderedAssets[] = $id;
        return true;
    }

    public static function parseShortcodes($content)
    {
        if (empty($content)) return '';

        // Remove parágrafos que envolvem shortcodes antes de parsear
        $content = preg_replace('/<p>\s*(\[[^\]]+\])\s*<\/p>/i', '$1', $content);

        /**
         * Regex explicada:
         * 1. \[([a-zA-Z0-9_\-]+) -> Captura o nome da tag
         * 2. ((?:\s+[a-zA-Z0-9_\-]+=(?:"[^"]*"|'[^']*'))*) -> Captura os atributos
         * 3. \s*\] -> Fecha a tag de abertura
         * 4. (?:(.*?)\[\/\1\])? -> (Opcional) Captura conteúdo interno até a tag de fechamento [/tag]
         */
        $pattern = '/\[([a-zA-Z0-9_\-]+)((?:\s+[a-zA-Z0-9_\-]+=(?:"[^"]*"|\'[^\']*\'))*)\s*\](?:(.*?)\[\/\1\])?/is';

        return preg_replace_callback($pattern, function($matches) {
            $tag = strtolower($matches[1]);
            $attrsString = $matches[2] ?? '';
            $innerContent = $matches[3] ?? null; // Conteúdo entre [tag] e [/tag]

            $attributes = self::parseAttributes($attrsString);

            return self::renderShortcode($tag, $attributes, $innerContent);
        }, $content);
    }

    private static function parseAttributes($attrsString)
    {
        $attributes = [];
        if (preg_match_all('/([a-zA-Z0-9_\-]+)=(?:"([^"]*)"|\'([^\']*)\')/', $attrsString, $attrMatches, PREG_SET_ORDER)) {
            foreach ($attrMatches as $attr) {
                // Limpa as aspas e protege contra XSS básico se necessário
                $attributes[$attr[1]] = $attr[2] !== '' ? $attr[2] : ($attr[3] ?? '');
            }
        }
        return $attributes;
    }

    private static function renderShortcode($tag, $attributes, $content = null)
    {
        $method = "render" . Str::studly($tag);
        if (method_exists(self::class, $method)) {
            return self::{$method}($attributes, $content);
        }

        $viewPath = "components.shortcodes." . $tag;
        if (view()->exists($viewPath)) {
            \Log::info('renderShortcode', ["path" => $viewPath]);
            return view($viewPath, [
                ...$attributes,
                'attr' => $attributes,
                'content' => $content
            ])->render();
        }

        return $content ? "[{$tag}]{$content}[/{$tag}]" : "[{$tag}]";
    }

    /**
     * Higieniza o HTML antes de salvar no banco de dados.
     * Remove parágrafos vazios, espaços fantasmas e limpa shortcodes.
     */
    public static function sanitizeForStorage($content)
    {
        if (empty($content)) return '';

        // 1. Remove parágrafos que contêm apenas espaços, quebras de linha ou &nbsp;
        // Isso mata o famoso <p>&nbsp;</p> do TinyMCE
        $content = preg_replace('/<p>(&nbsp;|\s)*<\/p>/i', '', $content);

        // 2. Remove parágrafos que envolvem apenas shortcodes (Higiene total)
        // Transforma <p>[shortcode]</p> em [shortcode]
        $content = preg_replace('/<p>\s*(\[[^\]]+\])\s*<\/p>/i', '$1', $content);

        // 3. Remove espaços em branco no início e fim de blocos (opcional, mas bom)
        $content = trim($content);

        return $content;
    }
}
