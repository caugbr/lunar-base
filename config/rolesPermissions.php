<?php

/**
 * =============================================================================
 * SISTEMA DE ROLES (PERFIS) E PERMISSIONS (PERMISSÕES)
 * =============================================================================
 *
 * Este arquivo é a FONTE DA VERDADE para todo o controle de acesso do sistema.
 * Define quem pode fazer o quê, organizado em perfis e permissões granulares.
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
 * - 'editor'      => Gerencia todas as publicações, termos, mídias e páginas de qualquer autor.
 * - 'author'      => Cria e edita apenas conteúdo de sua autoria (posts, páginas, mídias) e termos.
 * - 'subscriber'  => Acesso mínimo. Apenas visualiza dashboard e edita próprio perfil.
 *
 * DICA: Novos roles podem ser adicionados por plugins em tempo de execução via:
 * config(['rolesPermissions.roles.meu_papel' => [...]])
 *
 * =============================================================================
 *
 * 2. PERMISSIONS BY ROLE (PERMISSÕES POR PERFIL)
 * -----------------------------------------------
 *
 * Mapeia cada role para um array de permissões que ele possui.
 *
 * REGRAS IMPORTANTES:
 * - O role 'admin' DEVE conter TODAS as permissões listadas em 'permissionGroups'.
 * - Outros roles recebem apenas o subconjunto necessário para sua função.
 * - Permissões não listadas aqui são NEGADAS implicitamente.
 *
 * PERMISSÕES EXISTENTES:
 * ----------------------
 *
 * 'view-dashboard'       => Acessar o painel principal administrativo.
 * 'manage-users'         => Criar, editar, excluir qualquer usuário.
 * 'view-reports'         => Ver relatórios e estatísticas gerais.
 * 'edit-profile'         => Alterar dados do próprio perfil.
 * 'manage-settings'      => Modificar configurações globais do sistema.
 * 'manage-pages'         => Criar e editar PÁGINAS de qualquer autor.
 * 'manage-posts'         => Criar e editar POSTS de qualquer autor.
 * 'manage-own-pages'     => Criar e editar apenas as PRÓPRIAS páginas.
 * 'manage-own-posts'     => Criar e editar apenas os PRÓPRIOS posts.
 * 'manage-media'         => Fazer upload e gerenciar TODOS os arquivos da biblioteca de mídia.
 * 'manage-own-media'     => Fazer upload e gerenciar apenas os PRÓPRIOS arquivos de mídia.
 * 'manage-taxonomies'    => Criar, editar e excluir a estrutura de taxonomias e termos.
 * 'manage-tax-terms'     => Criar e gerenciar apenas termos/categorias/tags das taxonomias existentes.
 *
 * DIFERENÇA CRÍTICA:
 * ------------------
 * 'manage-...'     => Acesso irrestrito a registros de todos os usuários (admin/editor).
 * 'manage-own-...' => Acesso escopado exclusivamente aos registros criados pelo próprio usuário (author).
 *
 * =============================================================================
 *
 * 3. PERMISSION GROUPS (GRUPOS DE PERMISSÕES)
 * -------------------------------------------
 *
 * Agrupa permissões por área funcional. Usado na interface de gerenciamento
 * de permissões para organizar visualmente o que cada role pode fazer.
 *
 * GRUPOS EXISTENTES:
 * ------------------
 *
 * 'dashboard'     => Acesso ao painel de controle.
 * 'users'         => Gerenciamento de usuários.
 * 'settings'      => Configurações globais do sistema.
 * 'reports'       => Relatórios e estatísticas.
 * 'publications'  => Criação e edição de conteúdo (páginas e posts).
 * 'media'         => Gestão e uploads na biblioteca de arquivos de mídia.
 * 'taxonomies'    => Gestão estrutural de taxonomias e termos.
 * 'profile'       => Edição do próprio perfil.
 *
 * =============================================================================
 *
 * COMO USAR NO CÓDIGO:
 * --------------------
 *
 * Verificar se usuário tem permissão (aceita string única, array ou separada por vírgula):
 *     if (auth()->user()->hasPermission('manage-posts')) { ... }
 *     if (auth()->user()->hasPermission(['manage-posts', 'manage-own-posts'])) { ... }
 *
 * Verificar se usuário tem role:
 *     if (auth()->user()->hasRole('admin')) { ... }
 *
 * Proteger rota por permissão (Middleware):
 *     Route::middleware('permission:manage-posts,manage-own-posts')->group(...);
 *
 * =============================================================================
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Roles (Perfis de Usuário)
    |--------------------------------------------------------------------------
    */
    'roles' => [
        'admin' => [
            'name' => 'Administrador',
            'description' => 'Acesso total ao sistema. Gerencia usuários, configurações e tem visibilidade completa.'
        ],
        'editor' => [
            'name' => 'Editor',
            'description' => 'Gerencia publicações, mídias, taxonomias e edita páginas e posts de qualquer autor.'
        ],
        'author' => [
            'name' => 'Autor',
            'description' => 'Gerencia publicações, mídias e páginas de sua autoria, além de gerenciar termos e categorias.'
        ],
        'subscriber' => [
            'name' => 'Assinante',
            'description' => 'Acesso básico somente para visualização do painel e edição do próprio perfil.'
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
            'manage-own-pages',
            'manage-own-posts',
            // Mídia
            'manage-media',
            'manage-own-media',
            // Taxonomias e Termos
            'manage-taxonomies',
            'manage-tax-terms',
        ],

        'editor' => [
            'view-dashboard',
            'view-reports',
            'edit-profile',
            'manage-pages',
            'manage-posts',
            'manage-media',
            'manage-taxonomies',
        ],

        'author' => [
            'view-dashboard',
            'edit-profile',
            'manage-own-pages',
            'manage-own-posts',
            'manage-own-media',
            'manage-tax-terms',
        ],

        'subscriber' => [
            'view-dashboard',
            'edit-profile',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Grupos de Permissions (Exibição e Documentação)
    |--------------------------------------------------------------------------
    */
    'permissionGroups' => [
        'dashboard' => [
            'view-dashboard' => 'Ver painel de controle administrativo',
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
            'manage-pages'     => 'Criar e editar todas as páginas',
            'manage-posts'     => 'Criar e editar todos os posts',
            'manage-own-pages' => 'Criar e editar suas próprias páginas',
            'manage-own-posts' => 'Criar e editar seus próprios posts',
        ],

        'media' => [
            'manage-media'     => 'Gerenciar toda a biblioteca de mídia e uploads',
            'manage-own-media' => 'Gerenciar apenas os próprios uploads de mídia',
        ],

        'taxonomies' => [
            'manage-taxonomies' => 'Gerenciar estrutura completa de taxonomias e termos',
            'manage-tax-terms'  => 'Gerenciar apenas termos de taxonomias',
        ],

        'profile' => [
            'edit-profile' => 'Editar dados e senha do próprio perfil',
        ],
    ],
];
