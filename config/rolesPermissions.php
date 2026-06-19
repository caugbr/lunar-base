<?php

// config/rolesPermissions.php

return [
    /*
    |--------------------------------------------------------------------------
    | Roles
    |--------------------------------------------------------------------------
    */
    'roles' => [
        'admin' => [
            'name' => 'Administrador',
            'description' => 'Acesso total ao sistema. Gerencia usuários, configurações e tem visibilidade completa.'
        ],
        'editor' => [
            'name' => 'Editor',
            'description' => 'Gerencia publicações, cria e edita páginas e posts.'
        ],
        'author' => [
            'name' => 'Autor',
            'description' => 'Gerencia publicações, cria e edita páginas e posts de sua autoria.'
        ],
        'subscriber' => [
            'name' => 'Assinante',
            'description' => 'Acesso somente ao seu perfil.'
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Permissions por Role
    |--------------------------------------------------------------------------
    | Admin deve conter TODAS as permissões listadas em 'permissionGroups'.
    */
    'permissionsByRole' => [
        'admin' => [
            // Painel
            'view-dashboard',
            // Usuários
            'manage-users',
            // Relatórios
            'view-reports',
            // Perfil
            'edit-profile',
            // Configurações
            'manage-settings',
            // Publicações
            'manage-pages',
            'manage-posts',
        ],

        'editor' => [
            'view-dashboard',
            'view-reports',
            'edit-profile',
            'manage-pages',
            'manage-posts',
        ],

        'author' => [
            'view-dashboard',
            'edit-profile',
            'manage-own-pages',
            'manage-own-posts',
        ],

        'subscriber' => [
            'edit-profile',
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Grupos de Permissions
    |--------------------------------------------------------------------------
    */
    'permissionGroups' => [
        'dashboard' => [
            'view-dashboard' => 'Ver painel de controle',
        ],

        'users' => [
            'manage-users' => 'Gerenciar usuários (criar, editar, excluir)',
        ],

        'settings' => [
            'manage-settings' => 'Alterar configurações globais do sistema',
        ],

        'reports' => [
            'view-reports' => 'Ver relatórios gerais e estatísticos',
        ],

        'publications' => [
            'manage-pages' => 'Criar e editar todas as páginas',
            'manage-posts' => 'Criar e editar todos os posts',
            'manage-own-pages' => 'Criar e editar suas próprias páginas',
            'manage-own-posts' => 'Criar e editar seus próprios posts',
        ],

        'profile' => [
            'edit-profile' => 'Editar dados do próprio perfil',
        ],
    ],
];
