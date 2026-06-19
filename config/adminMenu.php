<?php

return [
    'menu' => [
        [
            'title' => 'Sistema',
            'items' => [
                [
                    'label' => 'Dashboard',
                    'icon' => 'layout-dashboard',
                    'route' => 'admin.dashboard',
                    'active' => 'admin.dashboard',
                ],
                [
                    'label' => 'Páginas',
                    'icon' => 'file',
                    'route' => 'admin.pages.index',
                    'active' => 'admin.pages.*',
                ],
                [
                    'label' => 'Posts',
                    'icon' => 'files',
                    'route' => 'admin.posts.index',
                    'active' => 'admin.posts.*',
                ],
                [
                    'label' => 'Mídia',
                    'icon' => 'image', // ou 'folder-image', 'gallery-horizontal'
                    'route' => 'admin.media.index',
                    'active' => 'admin.media.*',
                    'permission' => 'manage-media', // ⚠️ Remova se ainda não usa sistema de permissões // Ajuste conforme a posição desejada no menu
                ],
                [
                    'label' => 'Taxonomias',
                    'icon' => 'tags',
                    'route' => 'admin.taxonomies.index',
                    'active' => 'admin.taxonomies.*',
                    'permission' => 'manage-pages',
                ],
                [
                    'label' => 'Formulários',
                    'icon' => 'form',
                    'route' => 'admin.forms.index',
                    'active' => 'admin.forms.*',
                    'permission' => 'manage-pages',
                ],
                [
                    'label' => 'Usuários',
                    'icon' => 'users',
                    'route' => 'admin.users.index',
                    'active' => 'admin.users.*',
                ],
                // [
                //     'label' => 'Meu Perfil',
                //     'icon' => 'user-pen',
                //     'route' => 'admin.profile.edit',
                //     'active' => 'admin.profile.edit',
                // ],
                [
                    'label' => 'Permissões',
                    'icon' => 'user-key',
                    'route' => 'admin.roles-permissions',
                    'active' => 'admin.roles-permissions',
                    'role' => 'admin',
                ],
                [
                    'label' => 'Configurações',
                    'icon' => 'settings',
                    'route' => 'admin.settings.index',
                    'active' => 'admin.settings.*',
                    // 'permission' => 'manage-settings',
                    'role' => 'admin',
                ],
                [
                    'label' => 'Logs',
                    'icon' => 'list-checks',
                    'route' => 'admin.logs.index',
                    'active' => 'admin.logs.*',
                    // 'permission' => 'manage-logs',
                    'role' => 'admin',
                ],
            ]
        ]
    ],
];
