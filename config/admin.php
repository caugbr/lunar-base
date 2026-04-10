<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configurações Gerais da Administração
    |--------------------------------------------------------------------------
    */

    'page_title' => 'Lunar Admin',
    'title' => 'Lunar Admin',
    'subtitle' => 'Painel de Controle',
    'logo' => '🌙', // ou caminho da imagem

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
        [
            'label' => 'Termos',
            'icon' => 'tag',
            'route' => 'admin.terms.index',
            'active' => 'admin.terms.*',
            'permission' => 'manage-pages',
            'order' => 4,
        ],
        [
            'label' => 'Usuários',
            'icon' => 'users',
            'route' => 'admin.users.index',
            'active' => 'admin.users.*',
            'permission' => 'manage-users',
            'order' => 5,
        ],
        [
            'label' => 'Meu Perfil',
            'icon' => 'user-pen',
            'route' => 'admin.profile.edit',
            'active' => 'admin.profile.edit',
            'permission' => 'edit-profile',
            'order' => 6,
        ],
        [
            'label' => 'Configurações',
            'icon' => 'settings',
            'route' => 'admin.settings.index',
            'active' => 'admin.settings.*',
            'permission' => 'manage-settings',
            'order' => 99,
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
