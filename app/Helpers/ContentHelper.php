<?php

namespace App\Helpers;

use App\Traits\Shortcodes;
use Illuminate\Support\Str;

class ContentHelper
{
    use Shortcodes;

    // Guarda os IDs de assets que já foram renderizados nesta requisição
    protected static $renderedAssets = [];
    protected static $registeredShortcodes = [];

    /**
     * Registra um novo shortcode dinâmico (comum para uso em ServiceProviders de Plugins).
     */
    public static function registerShortcode(
        string $tag,
        callable $callback,
        string $description = '',
        string $example = '',
        array $attributes = [] // 💡 Novo parâmetro adicionado!
    ) {
        $tag = strtolower($tag);
        if (isset(self::$registeredShortcodes[$tag])) {
            return false;
        }

        // Armazena o callback junto aos metadados e o novo esquema de atributos
        self::$registeredShortcodes[$tag] = [
            'callback'    => $callback,
            'description' => $description,
            'example'     => $example,
            'attributes'  => $attributes, // 💡 Gravado aqui!
        ];

        return true;
    }

    /**
     * Retorna a lista unificada de todos os shortcodes disponíveis no sistema,
     * incluindo a definição de seus atributos suportados.
     */
    public static function getRegisteredShortcodes(): array
    {
        // 1. Definição estática com atributos estruturados para os shortcodes do Core (fixos)
        $coreShortcodes = [
            'embed' => [
                'type'        => 'Core',
                'description' => 'Incorpora de forma responsiva vídeos, áudios, mapas e scripts do YouTube, Vimeo, Spotify, Google Maps e GitHub Gists.',
                'example'     => '[embed]https://www.youtube.com/watch?v=dQw4w9WgXcQ[/embed]',
                'attributes'  => [
                    'url' => [
                        'label'       => 'URL da Mídia',
                        'type'        => 'text',
                        'placeholder' => 'Ex: https://www.youtube.com/watch?v=...',
                        'required'    => true
                    ]
                ]
            ],
            'link' => [
                'type'        => 'Core',
                'description' => 'Injeta elementos de tag <link> no cabeçalho do documento HTML (útil para CSS ou preloads).',
                'example'     => '[link rel="stylesheet" href="..."]',
                'attributes'  => [
                    'rel' => [
                        'label'   => 'Relação (rel)',
                        'type'    => 'text',
                        'default' => 'stylesheet'
                    ],
                    'href' => [
                        'label'       => 'Caminho do Arquivo (href)',
                        'type'        => 'text',
                        'placeholder' => 'Ex: /css/custom.css',
                        'required'    => true
                    ]
                ]
            ],
            'script' => [
                'type'        => 'Core',
                'description' => 'Injeta elementos de tag <script> assíncronos ou inline de forma segura e controlada no rodapé.',
                'example'     => '[script src="..." id="..."][/script]',
                'attributes'  => [
                    'src' => [
                        'label'       => 'Caminho do Arquivo (src)',
                        'type'        => 'text',
                        'placeholder' => 'Ex: /js/custom.js',
                        'required'    => true
                    ],
                    'id' => [
                        'label'       => 'ID de Identificação',
                        'type'        => 'text',
                        'placeholder' => 'Opcional (ex: custom-script)'
                    ]
                ]
            ],
            'style' => [
                'type'        => 'Core',
                'description' => 'Injeta regras de estilo CSS inline de forma isolada na renderização da página.',
                'example'     => '[style].classe-css { color: red; }[/style]',
                'attributes'  => [
                    'id' => [
                        'label'       => 'ID de Identificação',
                        'type'        => 'text',
                        'placeholder' => 'Opcional (ex: custom-style)'
                    ]
                ]
            ],
        ];

        // 2. Mapeia os shortcodes dinâmicos que foram ativados por plugins
        $pluginShortcodes = [];
        foreach (self::$registeredShortcodes as $tag => $data) {
            $pluginShortcodes[$tag] = [
                'type'        => 'Plugin',
                'description' => $data['description'] ?: 'Sem descrição fornecida.',
                'example'     => $data['example'] ?: "[{$tag}]",
                'attributes'  => $data['attributes'] ?? [], // 💡 Mapeia o esquema de atributos do plugin!
            ];
        }

        // 3. Mescla e ordena alfabeticamente
        $all = array_merge($coreShortcodes, $pluginShortcodes);
        ksort($all);

        return $all;
    }

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

    /**
     * Processa o conteúdo em busca de shortcodes no formato [tag] ou [tag]conteúdo[/tag]
     */
    public static function parseShortcodes($content)
    {
        if (empty($content)) return '';

        // 💡 CORREÇÃO: Remove parágrafos que envolvem shortcodes (suporta fechamento e auto-fechados)
        $content = preg_replace('/<p>\s*(\[[^\]]+\](?:.*?\[\/[^\]]+\])?)\s*<\/p>/is', '$1', $content);

        $pattern = '/\[([a-zA-Z0-9_\-]+)((?:\s+[a-zA-Z0-9_\-]+=(?:"[^"]*"|\'[^\']*\'))*)\s*\](?:(.*?)\[\/\1\])?/is';

        return preg_replace_callback($pattern, function($matches) {
            $tag = strtolower($matches[1]);
            $attrsString = $matches[2] ?? '';
            $innerContent = $matches[3] ?? null;

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
        $tag = strtolower($tag);

        // 1. Verifica se existe um shortcode registrado por um plugin
        if (isset(self::$registeredShortcodes[$tag])) {
            // 💡 AJUSTE: Agora busca o callback dentro da nova estrutura de array do registro
            return call_user_func(self::$registeredShortcodes[$tag]['callback'], $attributes, $content);
        }

        // 2. Fallback para métodos da Trait (Core)
        $method = "render" . Str::studly($tag);
        if (method_exists(self::class, $method)) {
            return self::{$method}($attributes, $content);
        }

        // 3. Fallback para views no core
        $viewPath = "components.shortcodes." . $tag;
        if (view()->exists($viewPath)) {
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
        $content = preg_replace('/<p>(&nbsp;|\s)*<\/p>/i', '', $content);

        // 2. Remove parágrafos que envolvem apenas shortcodes
        $content = preg_replace('/<p>\s*(\[[^\]]+\])\s*<\/p>/i', '$1', $content);

        // 3. Remove espaços em branco nas extremidades
        $content = trim($content);

        return $content;
    }
}
