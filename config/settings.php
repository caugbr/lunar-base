<?php

return [
    'definitions' => [

        // ========== GERAL ==========
        'general' => [
            'title' => 'Configurações Gerais',
            'description' => 'Identidade e informações básicas do site',
            'icon' => 'settings', // Opcional: para usar na view
            'fields' => [
                [
                    'key' => 'site_name',
                    'type' => 'text',
                    'label' => 'Nome do site',
                    'description' => 'Nome exibido no título do site',
                    'default' => 'Meu Site',
                    'order' => 1,
                ],
                [
                    'key' => 'site_description',
                    'type' => 'textarea',
                    'label' => 'Descrição do site',
                    'description' => 'Descrição para SEO',
                    'default' => '',
                    'order' => 2,
                ],
                [
                    'key' => 'site_logo',
                    'type' => 'image',
                    'label' => 'Logo do site',
                    'description' => 'Logo principal do site',
                    'default' => '',
                    'order' => 3,
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
                    'default' => 100,
                    'attributes' => ['min' => 50, 'max' => 200, 'step' => 1],
                    'order' => 10,
                ],
                [
                    'key' => 'media_thumbnail_height',
                    'type' => 'number',
                    'label' => 'Altura do thumbnail',
                    'description' => 'Altura máxima do thumbnail',
                    'default' => 100,
                    'attributes' => ['min' => 50, 'max' => 200, 'step' => 1],
                    'order' => 11,
                ],
                [
                    'key' => 'media_crop_thumbnail',
                    'type' => 'checkbox',
                    'label' => 'Cortar imagem?',
                    'description' => 'Cortar imagem para corresponder ao tamanho',
                    'default' => true,
                    'order' => 12,
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
                    'order' => 13,
                ],
                [
                    'key' => 'media_quality',
                    'type' => 'number',
                    'label' => 'Qualidade das imagens',
                    'description' => 'Qualidade de compressão (1-100)',
                    'default' => 80,
                    'attributes' => ['min' => 1, 'max' => 100, 'step' => 1],
                    'order' => 14,
                ],
                [
                    'key' => 'media_auto_compress',
                    'type' => 'checkbox',
                    'label' => 'Compressão automática',
                    'description' => 'Comprimir imagens automaticamente no upload',
                    'default' => true,
                    'order' => 15,
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
                    'order' => 16,
                ],
            ],
        ],

        // ========== SOCIAL ==========
        'social' => [
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
                    'order' => 20,
                ],
                [
                    'key' => 'instagram_url',
                    'type' => 'url',
                    'label' => 'Instagram',
                    'description' => 'URL do perfil do Instagram',
                    'default' => '',
                    'order' => 21,
                ],
                [
                    'key' => 'youtube_url',
                    'type' => 'url',
                    'label' => 'Youtube',
                    'description' => 'URL do perfil do Youtube',
                    'default' => '',
                    'order' => 22,
                ],
                [
                    'key' => 'social_share_position',
                    'type' => 'select',
                    'label' => 'Posição dos botões sociais',
                    'default' => 'bottom',
                    'options' => [
                        'top' => 'Topo da página',
                        'bottom' => 'Rodapé da página',
                        'both' => 'Topo e rodapé',
                        'none' => 'Não exibir',
                    ],
                    'order' => 23,
                ],
            ],
        ],

        // ========== E-MAIL ==========
        'mail' => [
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
                    'order' => 30,
                ],
                [
                    'key' => 'mail_from_name',
                    'type' => 'text',
                    'label' => 'Nome do remetente',
                    'description' => 'Nome que aparece como remetente',
                    'default' => '',
                    'order' => 31,
                ],
            ],
        ],

    ],
];
