<?php

/**
 * =============================================================================
 * SISTEMA DE ROLES (PERFIS) E PERMISSIONS (PERMISSÕES)
 * =============================================================================
 *
 * Este arquivo é a FONTE DA VERDADE para todo o controle de acesso do sistema.
 * Define quem pode fazer o quê, organizado em perfis hierárquicos.
 *
 * ESTRUTURA GERAL:
 * ----------------
 *
 * 'roles'             => Define os perfis de usuário existentes.
 * 'permissionsByRole' => Mapeia quais permissões cada perfil possui.
 * 'permissionGroups'  => Agrupa permissões por área para exibição na interface.
 *
 * =============================================================================
 *
 * 1. ROLES (PERFIS)
 * -----------------
 *
 * Cada perfil é uma chave única com nome e descrição:
 *
 * 'nome_do_role' => [
 *     'name'        => 'Nome Exibido',      // Obrigatório. Título na interface.
 *     'description' => 'Descrição',         // Obrigatório. Explicativo do perfil.
 * ],
 *
 * Perfis padrão do sistema:
 * - 'admin'       => Acesso total. Gerencia usuários, configurações, visibilidade completa.
 * - 'editor'      => Gerencia todas as publicações. Cria e edita páginas e posts de qualquer autor.
 * - 'author'      => Gerencia próprias publicações. Cria e edita apenas conteúdo de sua autoria.
 * - 'subscriber'  => Acesso mínimo. Apenas visualiza dashboard e edita próprio perfil.
 *
 * DICA: Novos roles podem ser adicionados aqui, mas precisam ser referenciados
 * em 'permissionsByRole' para ter permissões atribuídas.
 *
 * =============================================================================
 *
 * 2. PERMISSIONS BY ROLE (PERMISSÕES POR PERFIL)
 * -----------------------------------------------
 *
 * Mapeia cada role para um array de permissões que ele possui.
 *
 * 'nome_do_role' => [
 *     'nome-da-permissao',
 *     'outra-permissao',
 *     // ...
 * ],
 *
 * REGRAS IMPORTANTES:
 * - O role 'admin' DEVE conter TODAS as permissões listadas em 'permissionGroups'.
 * - Outros roles recebem apenas o subconjunto necessário para sua função.
 * - Permissões não listadas aqui são NEGADAS implicitamente.
 *
 * PERMISSÕES EXISTENTES:
 * ----------------------
 *
 * 'view-dashboard'       => Acessar o painel principal.
 * 'manage-users'         => Criar, editar, excluir qualquer usuário.
 * 'view-reports'         => Ver relatórios e estatísticas gerais.
 * 'edit-profile'         => Alterar dados do próprio perfil.
 * 'manage-settings'      => Modificar configurações globais do sistema.
 * 'manage-pages'         => Criar e editar PÁGINAS de qualquer autor.
 * 'manage-posts'         => Criar e editar POSTS de qualquer autor.
 * 'manage-own-pages'     => Criar e editar apenas PRÓPRIAS páginas.
 * 'manage-own-posts'     => Criar e editar apenas PRÓPRIOS posts.
 *
 * DIFERENÇA CRÍTICA:
 * ------------------
 * 'manage-pages'     => Acesso a TODAS as páginas (editor/admin).
 * 'manage-own-pages' => Acesso apenas às próprias páginas (author).
 *
 * Mesma lógica para 'manage-posts' vs 'manage-own-posts'.
 *
 * =============================================================================
 *
 * 3. PERMISSION GROUPS (GRUPOS DE PERMISSÕES)
 * -------------------------------------------
 *
 * Agrupa permissões por área funcional. Usado na interface de gerenciamento
 * de permissões para organizar visualmente o que cada role pode fazer.
 *
 * 'nome_do_grupo' => [
 *     'nome-da-permissao' => 'Label Descritivo',
 *     // ...
 * ],
 *
 * GRUPOS EXISTENTES:
 * ------------------
 *
 * 'dashboard'     => Acesso ao painel.
 * 'users'         => Gerenciamento de usuários.
 * 'settings'      => Configurações globais.
 * 'reports'       => Relatórios e estatísticas.
 * 'publications'  => Criação e edição de conteúdo (páginas e posts).
 * 'profile'       => Edição do próprio perfil.
 *
 * DICA: Ao criar nova permissão, adicione-a em TRES lugares:
 * 1. Em 'permissionGroups' (no grupo adequado, com label descritivo).
 * 2. No array de 'admin' em 'permissionsByRole' (admin deve ter tudo).
 * 3. Nos outros roles que precisam dessa permissão.
 *
 * =============================================================================
 *
 * COMO USAR NO CÓDIGO:
 * --------------------
 *
 * Verificar se usuário tem permissão:
 *     if (auth()->user()->permission('manage-pages')) {
 *         // permite ação...
 *     }
 *
 * Verificar se usuário tem role:
 *     if (auth()->user()->hasRole('admin')) {
 *         // permite ação...
 *     }
 *
 * Proteger rota por permissão:
 *     Route::middleware('permission:manage-users')->group(function () {
 *         // rotas protegidas...
 *     });
 *
 * Proteger rota por role:
 *     Route::middleware('role:admin')->group(function () {
 *         // rotas protegidas...
 *     });
 *
 * =============================================================================
 */

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
            'view-dashboard',
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
