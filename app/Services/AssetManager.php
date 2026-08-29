<?php

namespace App\Services;

class AssetManager
{
    protected array $styles = [];
    protected array $scripts = [];
    protected array $inlineStyles = [];
    protected array $inlineScripts = [];

    /**
     * Enfileira uma folha de estilo CSS
     */
    public function addStyle(string $handle, string $src, array $deps = [], string $version = '1.0.0', string $media = 'all'): void
    {
        if (!isset($this->styles[$handle])) {
            $this->styles[$handle] = [
                'src' => $src,
                'deps' => $deps,
                'version' => $version,
                'media' => $media,
            ];
        }
    }

    /**
     * Enfileira um script JS
     */
    public function addScript(string $handle, string $src, array $deps = [], string $version = '1.0.0', bool $inFooter = true, bool $defer = false): void
    {
        if (!isset($this->scripts[$handle])) {
            $this->scripts[$handle] = [
                'src' => $src,
                'deps' => $deps,
                'version' => $version,
                'in_footer' => $inFooter,
                'defer' => $defer,
            ];
        }
    }

    /**
     * Adiciona CSS inline associado a um handle
     */
    public function addInlineStyle(string $css): void
    {
        $this->inlineStyles[] = $css;
    }

    /**
     * Adiciona JS inline
     */
    public function addInlineScript(string $js): void
    {
        $this->inlineScripts[] = $js;
    }

    /**
     * Renderiza todas as tags <link rel="stylesheet">
     */
    public function renderStyles(): string
    {
        $html = '';

        foreach ($this->styles as $handle => $style) {
            $src = $style['src'];
            if ($style['version']) {
                $src .= (str_contains($src, '?') ? '&' : '?') . 'v=' . $style['version'];
            }
            $html .= sprintf(
                '<link rel="stylesheet" id="%s-css" href="%s" media="%s">' . "\n",
                e($handle),
                e($src),
                e($style['media'])
            );
        }

        if (!empty($this->inlineStyles)) {
            $html .= "<style>\n" . implode("\n", $this->inlineStyles) . "\n</style>\n";
        }

        return $html;
    }

    /**
     * Renderiza todas as tags <script> (para o Header ou Footer)
     */
    public function renderScripts(bool $footer = true): string
    {
        $html = '';

        foreach ($this->scripts as $handle => $script) {
            if ($script['in_footer'] === $footer) {
                $src = $script['src'];
                if ($script['version']) {
                    $src .= (str_contains($src, '?') ? '&' : '?') . 'v=' . $script['version'];
                }
                $defer = $script['defer'] ? ' defer' : '';
                $html .= sprintf(
                    '<script id="%s-js" src="%s"%s></script>' . "\n",
                    e($handle),
                    e($src),
                    $defer
                );
            }
        }

        if ($footer && !empty($this->inlineScripts)) {
            $html .= "<script>\n" . implode("\n", $this->inlineScripts) . "\n</script>\n";
        }

        return $html;
    }
}
