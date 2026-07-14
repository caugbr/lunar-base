<?php

/**
 * =============================================================================
 * CONFIGURAÇÕES DO FRONTEND
 * =============================================================================
 *
 * Este arquivo centraliza configurações para o site público (frontend).
 * Por enquanto contém apenas o menu principal, mas pode ser expandido
 * com outras configurações de exibição conforme necessário.
 *
 * MENU PRINCIPAL (mainMenu)
 * -------------------------
 *
 * Define os itens de navegação exibidos no header do site.
 *
 * CADA ITEM DEVE TER:
 * -------------------
 *
 * "label" - Obrigatório. Texto visível no link do menu.
 *
 * E UMA DAS TRÊS FORMAS DE DESTINO (nunca mais de uma):
 * ------------------------------------------------------
 *
 * 1. ROTA LARAVEL
 *    "route" => "nome.da.rota"
 *
 *    Para páginas estáticas do sistema com rota nomeada.
 *    Exemplo: "route" => "home"
 *    Exemplo: "route" => "blog.index"
 *
 * 2. SLUG (páginas ou posts)
 *    "slug" => "slug-do-conteudo"
 *
 *    Para páginas e posts criados dinamicamente na administração.
 *    Exemplo: "slug" => "sobre-nos"
 *    Exemplo: "slug" => "meu-primeiro-post"
 *
 *    Opcionalmente, para PÁGINAS apenas, pode-se usar:
 *    "namespace" => "namespace-da-pagina"
 *
 *    O namespace desambigua páginas com mesmo slug em contextos diferentes.
 *    Só pode ser usado junto com "slug" e só se aplica a páginas (não posts).
 *    Exemplo: "slug" => "contato", "namespace" => "institucional"
 *
 * 3. URL LIVRE
 *    "path" => "caminho/qualquer"
 *
 *    Para links externos, caminhos absolutos ou qualquer URL customizada.
 *    Exemplo: "path" => "blog"
 *    Exemplo: "path" => "https://externo.com"
 *
 * ESTRUTURA COMPLETA DE UM ITEM:
 * --------------------------------
 *
 * [
 *     "label"     => "Texto do Link",      // Obrigatório.
 *
 *     // Apenas UMA das opções abaixo:
 *     "route"     => "nome.da.rota",       // Rota Laravel
 *     // ou
 *     "slug"      => "slug-do-conteudo",   // Página ou post
 *     "namespace" => "namespace",          // Só páginas, opcional, requer slug
 *     // ou
 *     "path"      => "caminho/livre",      // URL qualquer
 * ],
 *
 * DICAS:
 * ------
 * - Use "route" para controllers do sistema.
 * - Use "slug" para conteúdo dinâmico da administração.
 * - "namespace" só funciona com páginas, nunca com posts.
 * - Use "path" para URLs externas ou quando não há rota/slug.
 * - Nunca combine mais de uma forma de destino no mesmo item.
 * - A ordem no array define a ordem de exibição no menu.
 *
 * =============================================================================
 */

return [
    "mainMenu" => [
        [
            "label" => "Início",
            "route" => 'home'
        ],
        [
            "label" => "Blog",
            "path" => "blog"
        ],
        [
            "label" => "Bem vindo",
            "slug" => "bem-vindo"
        ],
    ]
];
