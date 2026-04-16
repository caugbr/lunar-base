<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configurações Gerais da Administração
    |--------------------------------------------------------------------------
    */

    'page_title' => 'Lunar Admin - Laravel starter kit',
    'title' => 'Lunar Admin',
    'subtitle' => 'Painel de Controle',
    'logo' => '🌙', // ou html da imagem ou ícone

    /*
    |--------------------------------------------------------------------------
    | Menu de Navegação
    |--------------------------------------------------------------------------
    */

    'menu' => [
        [
            'label' => 'Dashboard',
            'icon' => 'layout-dashboard',
            'route' => 'admin.dashboard',
            'active' => 'admin.dashboard',
            'permission' => 'view-dashboard',
            'order' => 1,
        ],
        [
            'label' => 'Páginas',
            'icon' => 'file',
            'route' => 'admin.pages.index',
            'active' => 'admin.pages.*',
            'permission' => 'manage-pages',
            'order' => 2,
        ],
        [
            'label' => 'Taxonomias',
            'icon' => 'tags',
            'route' => 'admin.taxonomies.index',
            'active' => 'admin.taxonomies.*',
            'permission' => 'manage-pages',
            'order' => 3,
        ],
        // [
        //     'label' => 'Termos',
        //     'icon' => 'tag',
        //     'route' => 'admin.terms.index',
        //     'active' => 'admin.terms.*',
        //     'permission' => 'manage-pages',
        //     'order' => 4,
        // ],
        [
            'label' => 'Mídia',
            'icon' => 'image', // ou 'folder-image', 'gallery-horizontal'
            'route' => 'admin.media.index',
            'active' => 'admin.media.*',
            'permission' => 'manage-media', // ⚠️ Remova se ainda não usa sistema de permissões
            'order' => 5, // Ajuste conforme a posição desejada no menu
        ],
        [
            'label' => 'Usuários',
            'icon' => 'users',
            'route' => 'admin.users.index',
            // 'active' => 'admin.users.*',
            'active' => ['admin.users.*', 'admin.profile.edit'],
            'permission' => 'manage-users',
            'order' => 6,
        ],
        // [
        //     'label' => 'Meu Perfil',
        //     'icon' => 'user-pen',
        //     'route' => 'admin.profile.edit',
        //     'active' => 'admin.profile.edit',
        //     'permission' => 'edit-profile',
        //     'order' => 7,
        // ],
        [
            'label' => 'Configurações',
            'icon' => 'settings',
            'route' => 'admin.settings.index',
            'active' => 'admin.settings.*',
            'permission' => 'manage-settings',
            'order' => 8,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Rodapé
    |--------------------------------------------------------------------------
    */

    // 'footer' => [
    //     'copyright' => 'Lunar Admin',
    //     'version' => '1.0.0',
    //     'show_version' => true,
    // ],

    /*
    |--------------------------------------------------------------------------
    | Cores e Temas (opcional)
    |--------------------------------------------------------------------------
    */

    'colors' => [
        'primary' => '#4f46e5',   // indigo
        'secondary' => '#6b7280', // gray
        'success' => '#10b981',   // green
        'danger' => '#ef4444',    // red
        'warning' => '#f59e0b',   // amber
    ],

    /*
    |--------------------------------------------------------------------------
    | Paginação
    |--------------------------------------------------------------------------
    */

    'pagination' => [
        'per_page' => 20,
        'per_page_options' => [10, 20, 50, 100],
    ],
];
