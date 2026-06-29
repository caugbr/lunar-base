<?php

/**
 * =============================================================================
 * GUIA DE CONFIGURAÇÃO DE SETTINGS
 * =============================================================================
 *
 * Este arquivo define as configurações administráveis do sistema.
 * As configurações são organizadas em GRUPOS, que viram grupos ou abas na
 * interface, dependendo da configuração 'settings_in_tabs'.
 *
 * ESTRUTURA BÁSICA DE UM GRUPO:
 * --------------------------------
 * 'nome_do_grupo' => [
 *     'tab'       => 'Nome da Aba',      // Opcional. Define a aba onde o grupo aparece.
 *     'title'     => 'Título da Seção',   // Obrigatório. Título exibido na página.
 *     'description' => 'Descrição',       // Opcional. Texto explicativo abaixo do título.
 *     'icon'      => 'nome-do-icone',     // Opcional. Ícone Lucide (ex: 'settings', 'shield').
 *     'fields'    => [                    // Obrigatório. Array de campos.
 *         // ... campos aqui
 *     ],
 * ],
 *
 * A CHAVE DO CAMPO E A NOTAÇÃO DE PONTO:
 * ----------------------------------------
 * A função setting() usa o nome do grupo com notação de ponto:
 *     setting('grupo.chave')
 *
 * Isso permite ter campos com o mesmo nome em grupos diferentes, embora não
 * seja recomendado por questões de clareza.
 *
 * Exemplo: setting('general.site_name') ou setting('auth.2fa_enabled')
 *
 * TIPOS DE CAMPO E SUAS CONFIGURAÇÕES:
 * --------------------------------------
 *
 * 0. SUBTITLE (H3 - não é campo)
 *    [
 *        'type'  => 'subtitle',
 *        'label' => 'Título da sub seção'
 *    ]
 *
 * 1. TEXT (campo de texto simples)
 *    [
 *        'key'         => 'nome_da_chave', // Obrigatório. Usado em setting('grupo.chave').
 *        'type'        => 'text',          // Obrigatório.
 *        'label'       => 'Rótulo',        // Obrigatório. Nome exibido.
 *        'description' => 'Descrição',     // Opcional. Texto auxiliar.
 *        'default'     => 'valor padrão',  // Opcional. Valor inicial.
 *    ]
 *
 * 2. TEXTAREA (campo de texto longo)
 *    [
 *        'key'         => 'nome_da_chave',
 *        'type'        => 'textarea',
 *        'label'       => 'Rótulo',
 *        'description' => 'Descrição',
 *        'default'     => 'valor padrão',
 *    ]
 *
 * 3. NUMBER (campo numérico)
 *    [
 *        'key'         => 'nome_da_chave',
 *        'type'        => 'number',
 *        'label'       => 'Rótulo',
 *        'description' => 'Descrição',
 *        'default'     => 30,
 *        'attributes'  => [ // Opcional. Atributos HTML do input.
 *            'min'  => 15,
 *            'max'  => 120,
 *            'step' => 5,
 *        ],
 *    ]
 *
 * 4. EMAIL (campo de e-mail com validação)
 *    [
 *        'key'         => 'nome_da_chave',
 *        'type'        => 'email',
 *        'label'       => 'Rótulo',
 *        'description' => 'Descrição',
 *        'default'     => '',
 *    ]
 *
 * 5. URL (campo de URL com validação)
 *    [
 *        'key'         => 'nome_da_chave',
 *        'type'        => 'url',
 *        'label'       => 'Rótulo',
 *        'description' => 'Descrição',
 *        'default'     => '',
 *    ]
 *
 * 6. PASSWORD (campo de senha, mascara o valor)
 *    [
 *        'key'         => 'nome_da_chave',
 *        'type'        => 'password',
 *        'label'       => 'Rótulo',
 *        'description' => 'Descrição',
 *        'default'     => '',
 *    ]
 *
 * 7. IMAGE (upload de imagem)
 *    [
 *        'key'         => 'nome_da_chave',
 *        'type'        => 'image',
 *        'label'       => 'Rótulo',
 *        'description' => 'Descrição',
 *        'default'     => '',
 *    ]
 *
 * 8. SWITCH (ligar/desligar, booleano)
 *    [
 *        'key'         => 'nome_da_chave',
 *        'type'        => 'switch',
 *        'label'       => 'Rótulo',
 *        'description' => 'Descrição',
 *        'default'     => false,                    // true ou false.
 *        'active'      => 'Texto quando LIGADO',    // Opcional. Exibido ao lado do switch.
 *        'inactive'    => 'Texto quando DESLIGADO', // Opcional. Exibido ao lado do switch.
 *    ]
 *
 * 9. SELECT (dropdown de opções)
 *    [
 *        'key'         => 'nome_da_chave',
 *        'type'        => 'select',
 *        'label'       => 'Rótulo',
 *        'description' => 'Descrição',
 *        'default'     => 'valor_padrao',
 *        'options'     => [                 // Obrigatório. Opções do dropdown.
 *            'valor1' => 'Label 1',
 *            'valor2' => 'Label 2',
 *        ],
 *    ]
 *
 * 10. RADIO (botões de opção exclusiva)
 *     [
 *         'key'         => 'nome_da_chave',
 *         'type'        => 'radio',
 *         'label'       => 'Rótulo',
 *         'description' => 'Descrição',
 *         'default'     => 'valor_padrao',
 *         'options'     => [                 // Obrigatório.
 *             'valor1' => 'Label 1',
 *             'valor2' => 'Label 2',
 *         ],
 *     ]
 *
 * 11. CHECKBOX (caixas de seleção múltipla)
 *     [
 *         'key'         => 'nome_da_chave',
 *         'type'        => 'checkbox',
 *         'label'       => 'Rótulo',
 *         'description' => 'Descrição',
 *         'default'     => ['valor1'],      // Array de valores selecionados.
 *         'options'     => [                // Obrigatório.
 *             'valor1' => 'Label 1',
 *             'valor2' => 'Label 2',
 *         ],
 *     ]
 *
 * CONFIGURAÇÕES AVANÇADAS POR CAMPO:
 * ------------------------------------
 *
 * depends_on (habilita/desabilita campo baseado em outro):
 * ---------------------------------------------------------
 * Habilita este campo SOMENTE se outro campo tiver um valor específico.
 * Se a condição não for atendida, o campo fica desabilitado (não escondido).
 *
 *     'depends_on' => [
 *         'field'    => 'nome_do_outro_campo',  // Chave do campo que controla.
 *         'operator' => '===',                  // Operador: '===', '==', '!==', '!=', '>', '<', '>=' ou '<='.
 *         'value'    => true,                   // Valor esperado para HABILITAR este campo.
 *     ],
 *
 * Exemplo prático: habilitar chaves Turnstile apenas se CAPTCHA estiver ativo.
 *
 * warn_on_change (aviso ao alterar valor):
 * -----------------------------------------
 * Exibe um modal de confirmação quando o usuário tenta mudar o valor.
 *
 *     'warn_on_change' => 'Mensagem de aviso ao alterar este valor.',
 *
 * Exemplo prático: avisar que mudar a base de URLs quebra links antigos.
 *
 * COMO USAR AS SETTINGS NO CÓDIGO:
 * ----------------------------------
 *
 * No PHP (controllers, models, etc.):
 *     $valor = setting('grupo.chave', 'valor_padrao');
 *
 * No Blade (views):
 *     {{ setting('grupo.chave', 'valor_padrao') }}
 *
 * Exemplo real:
 *     if (setting('auth.use_captcha', false)) {
 *         // valida CAPTCHA...
 *     }
 *
 * DICAS:
 * ------
 * - Use uma 'key' descritiva e, se necessário, prefixada pelo contexto: '2fa_enabled'.
 * - 'default' é usado quando nenhum valor foi salvo no banco.
 * - 'tab' aparece no rótulo da aba, quando agrupado visualmente em abas, se 'settings_in_tabs' está ativo.
 * - 'icon' usa nomes do Lucide: 'settings', 'shield', etc. (https://lucide.dev/icons/)
 */

return [
    'definitions' => [

        // ========== GERAL ==========
        'general' => [
            'tab' => 'Geral',
            'title' => 'Configurações Gerais',
            'description' => 'Identidade e informações básicas do site',
            'icon' => 'settings',
            'fields' => [
                [
                    'key' => 'site_name',
                    'type' => 'text',
                    'label' => 'Nome do site',
                    'description' => 'Nome exibido no título da administração',
                    'default' => 'Lunar Base',
                ],
                [
                    'key' => 'site_description',
                    'type' => 'textarea',
                    'label' => 'Descrição do site',
                    'description' => 'Descrição mostrada abaixo no nome (SEO)',
                    'default' => 'Laravel Starter Kit',
                ],
                [
                    'key' => 'site_thumbnail',
                    'type' => 'image',
                    'label' => 'Imagem padrão',
                    'description' => 'Thumbnail padrão do site (SEO)',
                    'default' => '',
                ],
                [
                    'key' => 'site_theme',
                    'type' => 'select',
                    'label' => 'Tema padrão',
                    'description' => 'Tema inicial do site (o usuário pode escolher)',
                    'default' => 'dark',
                    'options' => ['light' => 'Claro', 'dark' => 'Escuro'],
                ],
                [
                    'key' => 'footer_text',
                    'type' => 'text',
                    'label' => 'Texto no footer',
                    'description' => 'Texto opcional no footer (ao lado do copyright). HTML não é permitido, mas URLs e emails viram links automaticamente.',
                    'default' => '',
                ],
                [
                    'key' => 'cookies_consent',
                    'type' => 'switch',
                    'label' => 'Consentimento para cookies',
                    'description' => 'Janela de consentimento de cookies. É preciso configurar os scripts que instalam cookies no navegador em <code>config/scripts.php</code>.',
                    'default' => true,
                    'active' => 'Usar',
                    'inactive' => 'Não usar',
                ],
            ],
        ],

        // ========== NAVIGATION ==========
        'navigation' => [
            'tab' => 'Navegação',
            'title' => 'Navegação',
            'description' => 'Detalhes sobre a navegação na admin',
            'icon' => 'signpost',
            'fields' => [
                [
                    'key' => 'save_search_params',
                    'type' => 'switch',
                    'label' => 'Salvar busca',
                    'description' => 'Deixe marcado para preservar os parâmetros de busca ao navegar.',
                    'default' => true,
                    'active' => 'Salvar navegação',
                    'inactive' => 'Não salvar',
                ],
                [
                    'key' => 'settings_in_tabs',
                    'type' => 'switch',
                    'label' => 'Configurações em abas',
                    'description' => 'Marque para exibir essa página em abas por assunto.',
                    'default' => false,
                    'active' => 'Abas',
                    'inactive' => 'Sequencial',
                ],
            ],
        ],

        // ========== TWO FACTOR AUTHENTICATION ==========
        'auth' => [
            'tab' => 'Autenticação',
            'title' => 'Reforços na autenticação',
            'description' => 'Configurações de segurança no login',
            'icon' => 'shield',
            'fields' => [
                [
                    'key' => 'use_captcha',
                    'type' => 'switch',
                    'label' => 'Usar CAPTCHA',
                    // 'description' => 'CAPTCHA é um mecanismo que verifica se quem está fazendo login é uma pessoa.',
                    'description' => 'CAPTCHA é um mecanismo que verifica se quem está fazendo login é uma pessoa. Requer conta Cloudflare e widget Turnstile — <a href="https://developers.cloudflare.com/turnstile/get-started/" target="_blank">entenda como fazer (inglês)</a>.',
                    'default' => false,
                    'active' => 'Usar CAPTCHA no login.',
                    'inactive' => 'Não usar',
                ],
                [
                    'key' => 'turnstile_site_key',
                    'type' => 'text',
                    'label' => 'Turnstile Site Key',
                    'description' => 'Chave pública do widget Cloudflare Turnstile.',
                    'default' => '',
                    'depends_on' => [
                        'field' => 'use_captcha',
                        'operator' => '===',
                        'value' => true,
                    ],
                ],
                [
                    'key' => 'turnstile_secret_key',
                    'type' => 'password',
                    'label' => 'Turnstile Secret Key',
                    'description' => 'Chave secreta para validação no servidor. Não compartilhe.',
                    'default' => '',
                    'depends_on' => [
                        'field' => 'use_captcha',
                        'operator' => '===',
                        'value' => true,
                    ],
                ],
                [
                    'key' => '2fa_enabled',
                    'type' => 'switch',
                    'label' => 'Ativar 2FA (Autenticação de dois fatores)',
                    'description' => 'Permite que usuários habilitem autenticação de dois fatores em seus perfis.',
                    'default' => false,
                    'active' => '2FA habilitado no sistema',
                    'inactive' => '2FA desabilitado',
                ],
                [
                    'key' => 'time_window',
                    'type' => 'number',
                    'label' => 'Duração do código (segundos)',
                    'description' => 'Tempo de validade de cada código TOTP. Padrão RFC 6238: 30 segundos.',
                    'default' => 30,
                    'attributes' => ['min' => 15, 'max' => 120, 'step' => 5],
                    'depends_on' => [
                        'field' => '2fa_enabled',
                        'operator' => '===',
                        'value' => true,
                    ],
                ],
                [
                    'key' => 'window_periods',
                    'type' => 'number',
                    'label' => 'Períodos de tolerância',
                    'description' => 'Quantos períodos de tempo anteriores/futuros aceitar. 1 = tolerante, 2 = muito tolerante a desvio de relógio.',
                    'default' => 1,
                    'attributes' => ['min' => 0, 'max' => 3, 'step' => 1],
                    'depends_on' => [
                        'field' => '2fa_enabled',
                        'operator' => '===',
                        'value' => true,
                    ],
                ],
                [
                    'key' => 'max_attempts_per_minute',
                    'type' => 'number',
                    'label' => 'Tentativas por minuto',
                    'description' => 'Máximo de tentativas de código antes do bloqueio temporário.',
                    'default' => 5,
                    'attributes' => ['min' => 3, 'max' => 20, 'step' => 1],
                    'depends_on' => [
                        'field' => '2fa_enabled',
                        'operator' => '===',
                        'value' => true,
                    ],
                ],
                [
                    'key' => 'partial_login_timeout',
                    'type' => 'number',
                    'label' => 'Tempo para completar 2FA (minutos)',
                    'description' => 'Quanto tempo o usuário tem para digitar o código após informar a senha.',
                    'default' => 5,
                    'attributes' => ['min' => 1, 'max' => 30, 'step' => 1],
                    'depends_on' => [
                        'field' => '2fa_enabled',
                        'operator' => '===',
                        'value' => true,
                    ],
                ],
                [
                    'key' => 'qr_code_size',
                    'type' => 'number',
                    'label' => 'Tamanho do QR code (px)',
                    'description' => 'Largura/altura do QR code exibido no perfil.',
                    'default' => 200,
                    'attributes' => ['min' => 100, 'max' => 500, 'step' => 50],
                    'depends_on' => [
                        'field' => '2fa_enabled',
                        'operator' => '===',
                        'value' => true,
                    ],
                ],
                [
                    'key' => 'issuer',
                    'type' => 'text',
                    'label' => 'Nome no app autenticador',
                    'description' => 'Nome que aparece no Google Authenticator/Authy ao escanear o QR code.',
                    'default' => config('app.name', 'Lunar Base'),
                    'depends_on' => [
                        'field' => '2fa_enabled',
                        'operator' => '===',
                        'value' => true,
                    ],
                ],
            ],
        ],

        // ========== PERMALINKS ==========
        'permalinks' => [
            'tab' => 'Permalinks',
            'title' => 'Montagem dos links',
            'description' => 'Configurações para a montagem das URLs',
            'icon' => 'link',
            'fields' => [
                [
                    'key' => 'pages_base',
                    'type' => 'text',
                    'label' => 'Namespace para páginas',
                    'description' => 'Prefixo de URL padrão para o carregamento das páginas públicas.',
                    'default' => '',
                    'warn_on_change' => 'Ao mudar este valor, as URLs de todas as páginas irão mudar e se algum de seus usuários favoritou, seus links deixarão de funcionar. Tem certeza?'
                ],
                [
                    'key' => 'posts_base',
                    'type' => 'text',
                    'label' => 'Namespace para posts',
                    'description' => 'Prefixo de URL padrão para o carregamento dos posts do blog.',
                    'default' => 'post',
                    'warn_on_change' => 'Ao mudar este valor, as URLs de todos os posts irão mudar e se algum de seus usuários favoritou, seus links deixarão de funcionar. Tem certeza?'
                ],
                [
                    'key' => 'blog_base',
                    'type' => 'text',
                    'label' => 'Namespace para o blog',
                    'description' => 'Prefixo de URL padrão para o carregamento do blog.',
                    'default' => 'blog',
                    'warn_on_change' => 'Ao mudar este valor, a URL do blog vai mudar e se algum de seus usuários favoritou, seu link deixará de funcionar. Tem certeza?'
                ],
            ],
        ],

        // ========== LEITURA ==========
        'reading' => [
            'title' => 'Leitura',
            'description' => 'Configurações de leitura',
            'icon' => 'glasses',
            'fields' => [
                [
                    'key' => 'excerpt_size',
                    'type' => 'number',
                    'label' => 'Tamanho do resumo',
                    'description' => 'Quantidade máxima de caracteres para resumos de posts e páginas',
                    'default' => 160,
                    'attributes' => ['min' => 50, 'max' => 600, 'step' => 10],
                ],
                [
                    'key' => 'words_count',
                    'type' => 'number',
                    'label' => 'Palavras por minuto de leitura',
                    'description' => 'Quantidade de palavras por minuto para o cálculo de tempo de leitura',
                    'default' => 200,
                    'attributes' => ['min' => 50, 'max' => 400, 'step' => 10],
                ],
                [
                    'key' => 'pagination_max_items',
                    'type' => 'number',
                    'label' => 'Itens por página (administração)',
                    'description' => 'Quantidade máxima de itens para paginação em listagens na administração',
                    'default' => 15,
                    'attributes' => ['min' => 5, 'max' => 50],
                ],
                [
                    'key' => 'media_pagination_max_items',
                    'type' => 'number',
                    'label' => 'Itens de mídia por página (administração)',
                    'description' => 'Quantidade máxima de itens para paginação de mídia na administração',
                    'default' => 24,
                    'attributes' => ['min' => 6, 'max' => 60, 'step' => 6],
                ],
                [
                    'key' => 'posts_max_items',
                    'type' => 'number',
                    'label' => 'Posts por página no blog (frontend)',
                    'description' => 'Quantidade máxima de posts na paginação do blog',
                    'default' => 15,
                    'attributes' => ['min' => 5, 'max' => 50],
                ],
                [
                    'key' => 'post_use_reaction',
                    'type' => 'switch',
                    'active' => 'Sim',
                    'inactive' => 'Não',
                    'label' => 'Usar reações nos posts',
                    'description' => 'Habilita reações nos posts (Like)',
                    'default' => true,
                ],
                [
                    'key' => 'post_reaction_type',
                    'type' => 'select',
                    'options' => [
                        'thumbs' => 'Legal',
                        'heart' => 'Coração',
                        'star' => 'Estrela',
                    ],
                    'label' => 'Tipo de reação',
                    'description' => 'Se as reações estão habilitadas nos posts, que tipo usar?',
                    'default' => 'thumbs',
                    'depends_on' => [
                        'field' => 'post_use_reaction',
                        'operator' => '===',
                        'value' => true,
                    ],
                ],
                [
                    'key' => 'post_negative_reaction',
                    'type' => 'switch',
                    'active' => 'Positivo e negativo',
                    'inactive' => 'Apenas positivo',
                    'label' => 'Usar reação negativa',
                    'description' => 'Habilita reações negativas nos posts (Dislike)',
                    'default' => false,
                    'depends_on' => [
                        'field' => 'post_use_reaction',
                        'operator' => '===',
                        'value' => true,
                    ],
                ],
                [
                    'key' => 'unique_reaction',
                    'type' => 'switch',
                    'active' => 'Uma reação por visitante',
                    'inactive' => 'Reações ilimitadas',
                    'label' => 'Reação única',
                    'description' => 'Se ativado, cada visitante (identificado por hash de IP) pode reagir apenas uma vez. Se desativado, reações são ilimitadas e anônimas. O ideal é definir esse valor uma única vez.',
                    'default' => true,
                    'depends_on' => [
                        'field' => 'post_use_reaction',
                        'operator' => '===',
                        'value' => true,
                    ],
                    'warn_on_change' => 'Ao mudar esse valor, o esquema no banco de dados muda e os totais de reações também mudam.'
                ],
            ],
        ],

// ========== ACCESSIBILITY ==========
        'accessibility' => [
            'tab' => 'Acessibilidade',
            'title' => 'Acessibilidade',
            'description' => 'Ajuste o elemento que mostra o bloco de acessibilidade',
            'icon' => 'accessibility',
            'fields' => [
                [
                    'key' => 'position',
                    'type' => 'select',
                    'label' => 'Posição do elemento',
                    'description' => 'Local para exibir o bloco de acessibilidade.',
                    'default' => 'right-middle',
                    'options' => [
                        'right-middle' => 'No meio da tela, à direita',
                        'right-top' => 'No topo da tela, à direita',
                        'right-bottom' => 'No fundo da tela, à direita',
                        'left-middle' => 'No meio da tela, à esquerda',
                        'left-top' => 'No topo da tela, à esquerda',
                        'left-bottom' => 'No fundo da tela, à esquerda',
                    ]
                ],
                [
                    'key' => 'switch_themes',
                    'type' => 'switch',
                    'label' => 'Seletor de temas',
                    'description' => 'Exibir seletor para o usuário decidir pelo tema claro ou o escuro.',
                    'default' => false,
                    'active' => 'Mostrar',
                    'inactive' => 'Não mostrar',
                ],
                [
                    'key' => 'vlibras',
                    'type' => 'switch',
                    'label' => 'VLibras',
                    'description' => 'Plugin para ler textos em linguagem de sinais (Vlibras).',
                    'default' => false,
                    'active' => 'Ativar',
                    'inactive' => 'Não ativar',
                ],
                [
                    'key' => 'increase_text_size',
                    'type' => 'switch',
                    'label' => 'Aumentar tamanho do texto',
                    'description' => 'Exibir links para aumentar o tamanho do texto.',
                    'default' => false,
                    'active' => 'Ativar',
                    'inactive' => 'Não ativar',
                ],
                [
                    'key' => 'text_size_steps',
                    'type' => 'number',
                    'label' => 'Quantidade de passos (Variações)',
                    'description' => 'Quantidade máxima de cliques para aumentar o texto antes de retornar ao tamanho original.',
                    'default' => 2,
                    'attributes' => [
                        'min' => 1,
                        'max' => 5,
                        'step' => 1,
                    ],
                    'depends_on' => [
                        'field' => 'increase_text_size',
                        'operator' => '===',
                        'value' => true,
                    ]
                ],
                [
                    'key' => 'text_size_step_value',
                    'type' => 'number',
                    'label' => 'Valor de cada passo (Pixels)',
                    'description' => 'Quantidade de pixels (px) adicionada ao tamanho do texto a cada clique.',
                    'default' => 4,
                    'attributes' => [
                        'min' => 1,
                        'max' => 12,
                        'step' => 1,
                    ],
                    'depends_on' => [
                        'field' => 'increase_text_size',
                        'operator' => '===',
                        'value' => true,
                    ]
                ],
            ],
        ],

        // ========== MÍDIA ==========
        'media' => [
            'title' => 'Mídia e Imagens',
            'description' => 'Controle de uploads, thumbnails e compressão',
            'icon' => 'image',
            'fields' => [
                [
                    'key' => 'media_thumbnail_width',
                    'type' => 'number',
                    'label' => 'Largura do thumbnail',
                    'description' => 'Largura máxima do thumbnail',
                    'default' => 300,
                    'attributes' => ['min' => 50, 'max' => 600, 'step' => 10],
                ],
                [
                    'key' => 'media_thumbnail_height',
                    'type' => 'number',
                    'label' => 'Altura do thumbnail',
                    'description' => 'Altura máxima do thumbnail',
                    'default' => 300,
                    'attributes' => ['min' => 50, 'max' => 600, 'step' => 10],
                ],
                [
                    'key' => 'media_crop_thumbnail',
                    'type' => 'switch',
                    'label' => 'Cortar imagem?',
                    'description' => 'Cortar imagem para corresponder ao tamanho',
                    'default' => true,
                ],
                [
                    'key' => 'media_crop_position',
                    'type' => 'select',
                    'label' => 'Posição do corte vertical',
                    'description' => 'Define qual parte da imagem será priorizada ao cortar',
                    'default' => 'center',
                    'options' => [
                        'top' => 'Parte superior',
                        'center' => 'Centro (recomendado)',
                        'bottom' => 'Parte inferior',
                    ],
                    'depends_on' => [
                        'field' => 'media_crop_thumbnail',
                        'operator' => '===',
                        'value' => true,
                    ],
                ],
                [
                    'key' => 'media_quality',
                    'type' => 'number',
                    'label' => 'Qualidade das imagens',
                    'description' => 'Qualidade de compressão (1-100)',
                    'default' => 80,
                    'attributes' => ['min' => 1, 'max' => 100, 'step' => 1],
                ],
                [
                    'key' => 'media_auto_compress',
                    'type' => 'switch',
                    'label' => 'Compressão automática',
                    'description' => 'Comprimir imagens automaticamente no upload',
                    'default' => true,
                ],
                [
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
                ],
            ],
        ],

        // ========== SOCIAL ==========
        'social' => [
            'tab' => 'Redes sociais',
            'title' => 'Redes Sociais',
            'description' => 'Links e comportamento de compartilhamento',
            'icon' => 'share-2',
            'fields' => [
                [
                    'key' => 'facebook_url',
                    'type' => 'url',
                    'label' => 'Facebook',
                    'description' => 'URL da página do Facebook',
                    'default' => '',
                ],
                [
                    'key' => 'instagram_url',
                    'type' => 'url',
                    'label' => 'Instagram',
                    'description' => 'URL do perfil do Instagram',
                    'default' => '',
                ],
                [
                    'key' => 'youtube_url',
                    'type' => 'url',
                    'label' => 'Youtube',
                    'description' => 'URL do perfil do Youtube',
                    'default' => '',
                ],
                [
                    'key' => 'tiktok_url',
                    'type' => 'url',
                    'label' => 'TikTok',
                    'description' => 'URL do perfil do TikTok',
                    'default' => '',
                ],
            ],
        ],

        // ========== E-MAIL ==========
        'mail' => [
            'tab' => 'E-mail',
            'title' => 'Configurações de E-mail',
            'description' => 'Remetente e envio de notificações',
            'icon' => 'mail',
            'fields' => [
                [
                    'key' => 'mail_from_address',
                    'type' => 'email',
                    'label' => 'E-mail de envio',
                    'description' => 'E-mail que aparece como remetente',
                    'default' => '',
                ],
                [
                    'key' => 'mail_from_name',
                    'type' => 'text',
                    'label' => 'Nome do remetente',
                    'description' => 'Nome que aparece como remetente',
                    'default' => '',
                ],
                [
                    'key' => 'mail_host',
                    'type' => 'text',
                    'label' => 'Servidor SMTP',
                    'description' => 'Ex: smtp.gmail.com, smtp.mailgun.org',
                    'default' => '',
                ],
                [
                    'key' => 'mail_port',
                    'type' => 'number',
                    'label' => 'Porta SMTP',
                    'description' => 'Ex: 587 (TLS), 465 (SSL)',
                    'default' => 587,
                ],
                [
                    'key' => 'mail_encryption',
                    'type' => 'select',
                    'label' => 'Criptografia',
                    'description' => 'Protocolo de segurança',
                    'default' => 'tls',
                    'options' => [
                        'tls' => 'TLS',
                        'ssl' => 'SSL',
                        '' => 'Nenhuma',
                    ],
                ],
                [
                    'key' => 'mail_username',
                    'type' => 'text',
                    'label' => 'Usuário SMTP',
                    'description' => 'Geralmente é o próprio e-mail',
                    'default' => '',
                ],
                [
                    'key' => 'mail_password',
                    'type' => 'password',
                    'label' => 'Senha SMTP',
                    'description' => 'Senha ou app password do servidor de email',
                    'default' => '',
                ],
            ],
        ],

    ],
];
