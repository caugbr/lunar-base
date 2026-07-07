<?php

namespace Plugins\Share;

use Illuminate\Support\ServiceProvider;
use App\Support\Settings;
use App\Support\HookManager;

class ShareServiceProvider extends ServiceProvider
{
    // 💡 Array Declarativo: Quer adicionar mais redes (ex: Pinterest, Reddit, Threads)?
    // Basta adicionar as configurações neste array e o respectivo SVG na view de ícones!
    protected array $networks = [
        'whatsapp' => [
            'label' => 'WhatsApp',
            'color' => '#25D366', // Cor para o efeito hover no CSS
            'url_template' => 'https://api.whatsapp.com/send?text={title}%20{url}',
        ],
        'facebook' => [
            'label' => 'Facebook',
            'color' => '#1877F2',
            'url_template' => 'https://www.facebook.com/sharer/sharer.php?u={url}',
        ],
        'twitter' => [
            'label' => 'X (Twitter)',
            'color' => '#000000',
            'url_template' => 'https://twitter.com/intent/tweet?text={title}&url={url}',
        ],
        'linkedin' => [
            'label' => 'LinkedIn',
            'color' => '#0A66C2',
            'url_template' => 'https://www.linkedin.com/sharing/share-offsite/?url={url}',
        ],
        'email' => [
            'label' => 'E-mail',
            'color' => '#718096',
            'url_template' => 'mailto:?subject={title}&body={url}',
        ],
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // 1. Carrega as views do plugin com namespace "share"
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'share');

        // 2. Injeta os switches de controle no admin de forma dinâmica
        $this->registerSettings();

        // 3. Registra o Hook para exibir os botões ao final do conteúdo do post
        HookManager::register('post.after_content', function($params) {
            $post = $params['post'] ?? null;
            if (!$post) return '';

            // Resolve título e URL encodados para não quebrar caracteres especiais
            $title = rawurlencode($post->title);
            $url = rawurlencode($post->url);

            // Filtra quais redes estão habilitadas no painel administrativo
            $activeNetworks = [];
            foreach ($this->networks as $key => $config) {
                if (setting("social.share_{$key}", true)) {
                    // Substitui os marcadores dinâmicos pelos dados reais do post
                    $shareUrl = str_replace(['{title}', '{url}'], [$title, $url], $config['url_template']);

                    $svgImg = file_exists(__DIR__ . "/resources/assets/images/{$key}.svg")
                                  ? asset("plugins/share/images/{$key}.svg")
                                  : '';
                    $activeNetworks[$key] = [
                        'label' => $config['label'],
                        'color' => $config['color'],
                        'share_url' => $shareUrl,
                        'svg_img' => $svgImg
                    ];
                }
            }

            // Se nenhuma rede estiver ativada, o hook retorna vazio
            if (empty($activeNetworks)) {
                return '';
            }

            if (view()->exists('share::links')) {
                return view('share::links', [
                    'type' => 'post',
                    'id' => $post->id,
                    'data' => $reactionData ?? [], // Mapeamento caso use reações
                    'activeNetworks' => $activeTheme = $activeNetworks
                ])->render();
            }

            return '';
        }, 'Share Plugin');
    }

    /**
     * Injeta de forma inteligente e declarativa os botões no grupo de redes sociais do admin
     */
    protected function registerSettings(): void
    {
        // Adiciona um divisor visual sob o menu "Redes sociais"
        Settings::add([
            'type' => 'subtitle',
            'icon' => 'share-2',
            'label' => 'Compartilhamento de Posts',
        ], 'social');

        // Adiciona os switches de ativação dinamicamente para cada rede
        foreach ($this->networks as $key => $config) {
            Settings::add([
                'key' => "share_{$key}",
                'type' => 'switch',
                'active' => 'Sim',
                'inactive' => 'Não',
                'label' => "Habilitar {$config['label']}",
                'description' => "Exibe o botão de compartilhamento do {$config['label']} nos posts.",
                'default' => true,
            ], 'social', 'Compartilhamento de Posts');
        }
    }
}
