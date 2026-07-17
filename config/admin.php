<?php

return [
    /**
     * =============================================================================
     * GUIA DE CONFIGURAÇÃO DO MENU DA ADMIN
     * =============================================================================
     *
     * Este arquivo define a estrutura do menu lateral da área administrativa.
     * O menu é organizado em SEÇÕES, e cada seção contém ITENS de navegação.
     *
     * ESTRUTURA BÁSICA:
     * -----------------
     *
     * 'menu' => [
     *     [
     *         'title' => 'Nome da Seção',     // Obrigatório. Título do grupo de itens.
     *         'items' => [                     // Obrigatório. Array de links do menu.
     *             // ... itens aqui
     *         ],
     *     ],
     * ],
     *
     * CONFIGURAÇÕES DE CADA ITEM:
     * -----------------------------
     *
     * [
     *     'label'      => 'Nome do Link',         // Obrigatório. Texto exibido no menu.
     *     'icon'       => 'nome-do-icone',        // Obrigatório. Ícone Lucide (ex: 'settings', 'users').
     *     'route'      => 'nome.da.rota',         // Obrigatório. Rota Laravel ao clicar.
     *     'active'     => 'prefixo.*',            // Obrigatório. Padrão que define se item está ativo.
     *                                              // Use '*' como wildcard (ex: 'admin.pages.*').
     *     'permission' => 'nome-da-permissao',    // Opcional. Exibe apenas se usuário tiver permissão.
     *     'role'       => 'nome-do-role',         // Opcional. Exibe apenas se usuário tiver role.
     *     'items'      => [...],                  // Opcional. Array de sub-itens (submenu).
     * ],
     *
     * SUBMENUS:
     * ---------
     *
     * Um item pode conter um array 'items' com sub-itens. No desktop, o submenu
     * aparece ao passar o mouse (hover) sobre o item pai. No mobile, os sub-itens
     * são sempre visíveis abaixo do item pai.
     *
     * Exemplo de item com submenu:
     * [
     *     'label'  => 'Páginas',
     *     'icon'   => 'file',
     *     'route'  => 'admin.pages.index',
     *     'active' => 'admin.pages.*',
     *     'items'  => [
     *         [
     *             'label'  => 'Nova página',
     *             'icon'   => 'file-plus',
     *             'route'  => 'admin.pages.create',
     *             'active' => 'admin.pages.create',
     *         ],
     *     ],
     * ],
     *
     * CONTROLE DE ACESSO:
     * -------------------
     *
     * 'permission' => Filtra por permissão específica do usuário.
     *                  Exemplo: 'manage-media', 'manage-pages'.
     *
     * 'role'       => Filtra por role do usuário.
     *                  Exemplo: 'admin', 'editor'.
     *
     * Se ambos forem omitidos, o item aparece para todos os usuários autenticados.
     *
     * ÍCONES:
     * -------
     *
     * Use nomes do Lucide: https://lucide.dev/icons/
     * Exemplos comuns: 'layout-dashboard', 'file', 'files', 'image', 'tags',
     * 'form', 'users', 'user-pen', 'user-key', 'settings', 'list-checks'.
     *
     * ROTAS E ACTIVE:
     * ---------------
     *
     * 'route'  => Nome completo da rota Laravel. Usado em route('nome.da.rota').
     *
     * 'active' => Prefixo que marca o item como ativo. Use '*' para corresponder
     *             a qualquer sub-rota. Exemplo: 'admin.pages.*' ativa para
     *             admin.pages.index, admin.pages.create, admin.pages.edit, etc.
     *
     * EXEMPLO COMPLETO:
     * -----------------
     *
     * [
     *     'label'      => 'Mídia',
     *     'icon'       => 'image',
     *     'route'      => 'admin.media.index',
     *     'active'     => 'admin.media.*',
     *     'permission' => 'manage-media',
     * ],
     *
     * DICAS:
     * ------
     * - Comente itens temporariamente com '//' em vez de remover.
     * - Ordene os itens na ordem desejada de exibição no menu.
     * - Agrupe logicamente por seções ('title') para organização visual.
     * - Use 'role' => 'admin' para itens restritos ao administrador.
     * - Submenus são úteis para ações rápidas (ex: "Nova página" dentro de "Páginas").
     *
     * =============================================================================
     */
    'menu' => [
        [
            'title' => 'Sistema',
            'items' => [
                [
                    'label' => 'Dashboard',
                    'icon' => 'layout-dashboard',
                    'route' => 'admin.dashboard.index',
                    'active' => 'admin.dashboard.index',
                ],
                [
                    'label' => 'Páginas',
                    'icon' => 'file',
                    'route' => 'admin.pages.index',
                    'active' => 'admin.pages.*',
                    'items' => [
                        [
                            'label' => 'Nova página',
                            'icon' => 'file-plus',
                            'route' => 'admin.pages.create',
                            'active' => 'admin.pages.create',
                        ]
                    ]
                ],
                [
                    'label' => 'Posts',
                    'icon' => 'files',
                    'route' => 'admin.posts.index',
                    'active' => 'admin.posts.*',
                    'items' => [
                        [
                            'label' => 'Novo post',
                            'icon' => 'file-plus',
                            'route' => 'admin.posts.create',
                            'active' => 'admin.posts.create',
                        ]
                    ]
                ],
                [
                    'label' => 'Mídia',
                    'icon' => 'image',
                    'route' => 'admin.media.index',
                    'active' => 'admin.media.*',
                    'permission' => 'manage-media',
                ],
                [
                    'label' => 'Taxonomias',
                    'icon' => 'tags',
                    'route' => 'admin.taxonomies.index',
                    'active' => 'admin.taxonomies.*',
                    'permission' => 'manage-pages',
                    'items' => [
                        [
                            'label'  => 'Nova Taxonomia',
                            'icon'   => 'tag',
                            'route'  => 'admin.taxonomies.create',
                            'active' => 'admin.taxonomies.create',
                            'permission' => 'manage-pages',
                        ]
                    ]
                ],
                [
                    'label' => 'Usuários',
                    'icon' => 'users',
                    'route' => 'admin.users.index',
                    'active' => 'admin.users.*',
                    'items' => [
                        [
                            'label'  => 'Novo usuário',
                            'icon'   => 'user-plus',
                            'route'  => 'admin.users.create',
                            'active' => 'admin.users.create',
                            'role'   => 'admin',
                        ]
                    ]
                ],
                [
                    'label' => 'Plugins',
                    'icon' => 'puzzle',
                    'route' => 'admin.plugins.index',
                    'active' => 'admin.plugins.*',
                    'items' => [
                        [
                            'label'  => 'Hooks',
                            'icon'   => 'fishing-hook',
                            'route'  => 'admin.hooks',
                            'active' => 'admin.hooks',
                            'role'   => 'admin',
                        ]
                    ]
                ],
                [
                    'label' => 'Temas',
                    'icon' => 'palette',
                    'route' => 'admin.themes.index',
                    'active' => 'admin.themes.*',
                ],
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

    "dashboard" =>  [
        "icon" => "layout-dashboard",
        "title" => "Dashboard",
        "subtitle" => "Visão geral do sistema",
        "cardTitle" => "Visão geral",
        "columns" => 4,
        "boxView" => "admin.dashboard.box"
    ],

    // admin skin
    "skin" => "default"
];
