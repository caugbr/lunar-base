<?php

return [
    'definitions' => [
        // ========== CONFIGURAÇÕES GERAIS ==========
        [
            'group' => 'general',
            'key' => 'site_name',
            'type' => 'text',
            'label' => 'Nome do site',
            'description' => 'Nome exibido no título do site',
            'default' => 'Meu Site',
            'order' => 1,
        ],
        [
            'group' => 'general',
            'key' => 'site_description',
            'type' => 'textarea',
            'label' => 'Descrição do site',
            'description' => 'Descrição para SEO',
            'default' => '',
            'order' => 2,
        ],
        [
            'group' => 'general',
            'key' => 'site_logo',
            'type' => 'image',
            'label' => 'Logo do site',
            'description' => 'Logo principal do site',
            'default' => '',
            'order' => 3,
        ],

        // ========== CONFIGURAÇÕES DE MÍDIA ==========
        [
            'group' => 'media',
            'key' => 'media_thumb_size',
            'type' => 'select',
            'label' => 'Tamanho do Thumbnail',
            'description' => 'Tamanho das miniaturas',
            'default' => '150',
            'options' => [
                '100' => '100x100',
                '150' => '150x150',
                '200' => '200x200',
                '300' => '300x300',
            ],
            'order' => 10,
        ],
        [
            'group' => 'media',
            'key' => 'media_quality',
            'type' => 'number',
            'label' => 'Qualidade das imagens',
            'description' => 'Qualidade de compressão (1-100)',
            'default' => 80,
            'attributes' => ['min' => 1, 'max' => 100, 'step' => 1],
            'order' => 11,
        ],
        [
            'group' => 'media',
            'key' => 'media_auto_compress',
            'type' => 'checkbox',
            'label' => 'Compressão automática',
            'description' => 'Comprimir imagens automaticamente no upload',
            'default' => true,
            'order' => 12,
        ],
        [
            'group' => 'media',
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
            'order' => 13,
        ],

        // ========== CONFIGURAÇÕES DE SOCIAL ==========
        [
            'group' => 'social',
            'key' => 'facebook_url',
            'type' => 'url',
            'label' => 'Facebook',
            'description' => 'URL da página do Facebook',
            'default' => '',
            'order' => 20,
        ],
        [
            'group' => 'social',
            'key' => 'instagram_url',
            'type' => 'url',
            'label' => 'Instagram',
            'description' => 'URL do perfil do Instagram',
            'default' => '',
            'order' => 21,
        ],
        [
            'group' => 'social',
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
            'order' => 22,
        ],

        // ========== CONFIGURAÇÕES DE EMAIL ==========
        [
            'group' => 'mail',
            'key' => 'mail_from_address',
            'type' => 'email',
            'label' => 'E-mail de envio',
            'description' => 'E-mail que aparece como remetente',
            'default' => '',
            'order' => 30,
        ],
        [
            'group' => 'mail',
            'key' => 'mail_from_name',
            'type' => 'text',
            'label' => 'Nome do remetente',
            'description' => 'Nome que aparece como remetente',
            'default' => '',
            'order' => 31,
        ],
    ],
];
