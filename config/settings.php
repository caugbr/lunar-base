<?php

return [
    'definitions' => [

        // ========== GERAL ==========
        'general' => [
            'tab' => 'Geral',
            'title' => 'Configurações Gerais',
            'description' => 'Identidade e informações básicas do site',
            'icon' => 'settings', // Opcional: para usar na view
            'fields' => [
                [
                    'key' => 'site_name',
                    'type' => 'text',
                    'label' => 'Nome do site',
                    'description' => 'Nome exibido no título da administração',
                    'default' => 'Lunar Base',
                ],
                [
                    'key' => 'site_description',
                    'type' => 'textarea',
                    'label' => 'Descrição do site',
                    'description' => 'Descrição mostrada abaixo no nome (SEO)',
                    'default' => 'Laravel Starter Kit',
                ],
                [
                    'key' => 'site_thumbnail',
                    'type' => 'image',
                    'label' => 'Imagem padrão',
                    'description' => 'Thumbnail padrão do site (SEO)',
                    'default' => '',
                ],
                [
                    'key' => 'site_theme',
                    'type' => 'select',
                    'label' => 'Tema padrão',
                    'description' => 'Tema inicial do site (o usuário pode escolher)',
                    'default' => 'dark',
                    'options' => ['light' => 'Claro', 'dark' => 'Escuro'],
                ],
            ],
        ],

        // ========== NAVIGATION ==========

        'navigation' => [
            'tab' => 'Navegação',
            'title' => 'Navegação',
            'description' => 'Detalhes sobre a navegação na admin',
            'icon' => 'signpost',
            'fields' => [
                [
                    'key' => 'use_captcha',
                    'type' => 'switch',
                    'label' => 'Usar CAPTCHA',
                    'description' => 'CAPTCHA é um mecanismo que verifica se quem está fazendo login é uma pessoa. Isso evita tentativas de invasão.',
                    'default' => false,
                    'active' => 'Usar CAPTCHA no login. Atenção, é preciso criar um app em Clouflare e definir no .env as variáveis TURNSTILE_*_KEY.',
                    'inactive' => 'Não usar',
                ],
                [
                    'key' => 'save_search_params',
                    'type' => 'switch',
                    'label' => 'Salvar busca',
                    'description' => 'Deixe marcado para preservar os parâmetros de busca ao navegar.',
                    'default' => true,
                    'active' => 'Salvar navegação',
                    'inactive' => 'Não salvar',
                ],
                [
                    'key' => 'settings_in_tabs',
                    'type' => 'switch',
                    'label' => 'Configurações em abas',
                    'description' => 'Marque para exibir essa página em abas por assunto.',
                    'default' => false,
                    'active' => 'Abas',
                    'inactive' => 'Sequencial',
                ],
            ],
        ],

        // ========== PERMALINKS ==========
        'permalinks' => [
            'tab' => 'Permalinks',
            'title' => 'Montagem dos links',
            'description' => 'Configurações para a montagem das URLs',
            'icon' => 'link',
            'fields' => [
                [
                    'key' => 'pages_base',
                    'type' => 'text',
                    'label' => 'Namespace para páginas',
                    'description' => 'Prefixo de URL padrão para o carregamento das páginas públicas.',
                    'default' => '',
                    'forbidden' => ['admin', 'login', 'logout', 'setting:navigation.posts_base', 'formulario', 'api', 'home'],
                ],
                [
                    'key' => 'posts_base',
                    'type' => 'text',
                    'label' => 'Namespace para posts',
                    'description' => 'Prefixo de URL padrão para o carregamento dos posts do blog.',
                    'default' => 'post',
                    'forbidden' => ['admin', 'login', 'logout', 'setting:navigation.pages_base', 'formulario', 'api', 'home'],
                ],
                [
                    'key' => 'blog_base',
                    'type' => 'text',
                    'label' => 'Namespace para o blog',
                    'description' => 'Prefixo de URL padrão para o carregamento do blog.',
                    'default' => 'blog',
                    'forbidden' => ['admin', 'login', 'logout', 'setting:navigation.pages_base', 'formulario', 'api', 'home'],
                ],
            ],
        ],

        // ========== MÍDIA ==========
        'media' => [
            'title' => 'Mídia e Imagens',
            'description' => 'Controle de uploads, thumbnails e compressão',
            'icon' => 'image',
            'fields' => [
                [
                    'key' => 'media_thumbnail_width',
                    'type' => 'number',
                    'label' => 'Largura do thumbnail',
                    'description' => 'Largura máxima do thumbnail',
                    'default' => 300,
                    'attributes' => ['min' => 50, 'max' => 600, 'step' => 10],
                ],
                [
                    'key' => 'media_thumbnail_height',
                    'type' => 'number',
                    'label' => 'Altura do thumbnail',
                    'description' => 'Altura máxima do thumbnail',
                    'default' => 300,
                    'attributes' => ['min' => 50, 'max' => 600, 'step' => 10],
                ],
                [
                    'key' => 'media_crop_thumbnail',
                    'type' => 'switch',
                    'label' => 'Cortar imagem?',
                    'description' => 'Cortar imagem para corresponder ao tamanho',
                    'default' => true,
                ],
                [
                    'key' => 'media_crop_position',
                    'type' => 'select',
                    'label' => 'Posição do corte vertical',
                    'description' => 'Define qual parte da imagem será priorizada ao cortar (horizontal sempre centralizado)',
                    'default' => 'center',
                    'options' => [
                        'top' => 'Parte superior',
                        'center' => 'Centro (recomendado)',
                        'bottom' => 'Parte inferior',
                    ],
                ],
                [
                    'key' => 'media_quality',
                    'type' => 'number',
                    'label' => 'Qualidade das imagens',
                    'description' => 'Qualidade de compressão (1-100)',
                    'default' => 80,
                    'attributes' => ['min' => 1, 'max' => 100, 'step' => 1],
                ],
                [
                    'key' => 'media_auto_compress',
                    'type' => 'switch',
                    'label' => 'Compressão automática',
                    'description' => 'Comprimir imagens automaticamente no upload',
                    'default' => true,
                ],
                [
                    'key' => 'media_formats',
                    'type' => 'radio',
                    'label' => 'Formato preferido',
                    'description' => 'Formato padrão para uploads',
                    'default' => 'webp',
                    'options' => [
                        'jpeg' => 'JPEG',
                        'png' => 'PNG',
                        'webp' => 'WebP',
                    ],
                ],
            ],
        ],

        // ========== SOCIAL ==========
        'social' => [
            'tab' => 'Social',
            'title' => 'Redes Sociais',
            'description' => 'Links e comportamento de compartilhamento',
            'icon' => 'share-2',
            'fields' => [
                [
                    'key' => 'facebook_url',
                    'type' => 'url',
                    'label' => 'Facebook',
                    'description' => 'URL da página do Facebook',
                    'default' => '',
                ],
                [
                    'key' => 'instagram_url',
                    'type' => 'url',
                    'label' => 'Instagram',
                    'description' => 'URL do perfil do Instagram',
                    'default' => '',
                ],
                [
                    'key' => 'youtube_url',
                    'type' => 'url',
                    'label' => 'Youtube',
                    'description' => 'URL do perfil do Youtube',
                    'default' => '',
                ],
                [
                    'key' => 'tiktok_url',
                    'type' => 'url',
                    'label' => 'TikTok',
                    'description' => 'URL do perfil do TikTok',
                    'default' => '',
                ],
            ],
        ],

        // ========== E-MAIL ==========
        'mail' => [
            'tab' => 'E-mail',
            'title' => 'Configurações de E-mail',
            'description' => 'Remetente e envio de notificações',
            'icon' => 'mail',
            'fields' => [
                [
                    'key' => 'mail_from_address',
                    'type' => 'email',
                    'label' => 'E-mail de envio',
                    'description' => 'E-mail que aparece como remetente',
                    'default' => '',
                ],
                [
                    'key' => 'mail_from_name',
                    'type' => 'text',
                    'label' => 'Nome do remetente',
                    'description' => 'Nome que aparece como remetente',
                    'default' => '',
                ],
                [
                    'key' => 'mail_host',
                    'type' => 'text',
                    'label' => 'Servidor SMTP',
                    'description' => 'Ex: smtp.gmail.com, smtp.mailgun.org',
                    'default' => '',
                ],
                [
                    'key' => 'mail_port',
                    'type' => 'number',
                    'label' => 'Porta SMTP',
                    'description' => 'Ex: 587 (TLS), 465 (SSL)',
                    'default' => 587,
                ],
                [
                    'key' => 'mail_encryption',
                    'type' => 'select',
                    'label' => 'Criptografia',
                    'description' => 'Protocolo de segurança',
                    'default' => 'tls',
                    'options' => [
                        'tls' => 'TLS',
                        'ssl' => 'SSL',
                        '' => 'Nenhuma',
                    ],
                ],
                [
                    'key' => 'mail_username',
                    'type' => 'text',
                    'label' => 'Usuário SMTP',
                    'description' => 'Geralmente é o próprio e-mail',
                    'default' => '',
                ],
                [
                    'key' => 'mail_password',
                    'type' => 'password',
                    'label' => 'Senha SMTP',
                    'description' => 'Senha ou app password do servidor de email',
                    'default' => '',
                ],
            ],
        ],

    ],
];
