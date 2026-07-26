<?php

namespace Plugins\PrismHighlight;

use Illuminate\Support\ServiceProvider;
use App\Helpers\ContentHelper;

class PrismHighlightServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'prismhighlight');

        ContentHelper::registerShortcode(
            'code',
            function ($attributes, $content) {
                return $this->renderCode($attributes, $content);
            },
            'Exibe bloco de código com syntax highlighting (estilo Mac ou Plain)',
            '[code lang="php" title="app.php" style="mac"]\n// seu código\n[/code]',
            [
                'lang' => [
                    'label'    => 'Linguagem',
                    'type'     => 'select',
                    'options'  => [
                        'php'        => 'PHP',
                        'javascript' => 'JavaScript',
                        'vue'        => 'Vue (SFC)',
                        'markup'     => 'HTML / XML',
                        'css'        => 'CSS',
                        'scss'       => 'SCSS',
                        'bash'       => 'Bash / Terminal',
                        'sql'        => 'SQL',
                        'json'       => 'JSON',
                    ],
                    'default'  => 'php',
                    'required' => true
                ],
                'title' => [
                    'label'       => 'Nome do arquivo (opcional)',
                    'type'        => 'text',
                    'placeholder' => 'ex: CauGuanabara.php',
                    'required'    => false
                ],
                'style' => [
                    'label'    => 'Estilo da Janela',
                    'type'     => 'select',
                    'options'  => [
                        'mac'   => 'Mac (com bolinhas vermelha/amarela/verde)',
                        'plain' => 'Plain (limpo, apenas o código)'
                    ],
                    'default'  => 'mac',
                    'required' => false
                ],
                'theme' => [
                    'label'    => 'Tema de Cores',
                    'type'     => 'select',
                    'options'  => [
                        'tomorrow'       => 'Tomorrow Night (Padrão Carbon, clean)',
                        'okaidia'        => 'Okaidia (Baseado no Monokai, clássico)',
                        'dracula'        => 'Dracula (Alto contraste, fundo roxo escuro)',
                        'one-dark'       => 'One Dark (Estilo Atom / VSCode)',
                        'solarizedlight' => 'Solarized Light (Claro, suave para os olhos)',
                        'default'        => 'Prism Default (Branco com sombras)',
                    ],
                    'default'  => 'tomorrow',
                    'required' => false
                ],
            ]
        );
    }

    protected function renderCode(array $attributes, string $content): string
    {
        $lang = strtolower($attributes['lang'] ?? 'php');
        $title = trim($attributes['title'] ?? '');
        $style = strtolower($attributes['style'] ?? 'mac');
        $theme = strtolower($attributes['theme'] ?? 'dark');

        $escapedContent = e(trim($content));
        $langClass = 'language-' . $lang;

        return view('prismhighlight::public.codeblock', [
            'lang'      => $lang,
            'langClass' => $langClass,
            'title'     => $title,
            'style'     => $style,
            'theme'     => $theme,
            'content'   => $escapedContent
        ])->render();
    }
}
