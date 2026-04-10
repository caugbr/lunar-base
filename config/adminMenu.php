<?php

return [
    'menu' => [
        [
            'label' => 'Dashboard',
            'icon' => 'layout-dashboard',
            'route' => 'admin.dashboard',
            'active' => 'admin.dashboard',
            'order' => 1,
        ],
        [
            'label' => 'Usuários',
            'icon' => 'users',
            'route' => 'admin.users.index',
            'active' => 'admin.users.*',
            'order' => 4,
        ],
        [
            'label' => 'Meu Perfil',
            'icon' => 'user-pen',
            'route' => 'admin.profile.edit',
            'active' => 'admin.profile.edit',
            'order' => 5,
        ],
        [
            'label' => 'Páginas',
            'icon' => 'file',
            'route' => 'admin.pages.index',
            'active' => 'admin.pages.*',
            'order' => 6,
        ],
    ],
];
