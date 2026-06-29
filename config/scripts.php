<?php

return [
    // 💡 Nível 1: Necessários (Sempre ativos, não dão direito de recusa)
    'essenciais' => [
        'title' => "Necessários",
        'description' => "Essenciais para o funcionamento básico do site, segurança, validação de formulários e recursos de acessibilidade.",
        'level' => 'required',
        'items' => [
            // 'captcha' => [
            //     'name' => 'Cloudflare Turnstile',
            //     'description' => 'Verificação de segurança em formulários para evitar envios automatizados (spam) e acessos maliciosos de robôs.',
            //     'src' => 'https://challenges.cloudflare.com/turnstile/v0/api.js',
            // ],
            'php_session' => [
                'name' => 'PHPSESSID',
                'description' => 'Mantém o identificador da sessão ativa do usuário para comunicação segura com o servidor.',
            ],
            'laravel_session' => [
                'name' => '[session_cookie]',
                'description' => 'Armazena dados criptografados da sessão do usuário no Laravel para persistência de login e preferências locais.',
            ],
            'xsrf_security' => [
                'name' => 'XSRF-TOKEN',
                'description' => 'Token de segurança contra ataques de falsificação de solicitação cruzada (CSRF).',
            ]
        ]
    ],

    // 💡 Nível 2: Analíticos (Opcionais)
    'analytics' => [
        'title' => "Estatísticas e Desempenho",
        'description' => "Nos ajudam a entender como os visitantes interagem com o site, coletando dados de navegação de forma anônima.",
        'level' => 'optional',
        'items' => [
            // 'google_analytics' => [
            //     'name' => 'Google Analytics (_ga, _gid)',
            //     'description' => 'Coleta dados de tráfego, páginas mais visitadas e tempo de permanência no site para fins de relatórios estatísticos de desempenho.',
            //     'src' => 'https://www.googletagmanager.com/gtag/js?id=G-SEUID',
            // ]
        ]
    ],

    // 💡 Nível 3: Marketing (Opcionais)
    'marketing' => [
        'title' => "Marketing e Publicidade",
        'description' => "Utilizados para exibir anúncios relevantes de acordo com seus interesses e medir o retorno de campanhas publicitárias.",
        'level' => 'optional',
        'items' => [
            // 'facebook_pixel' => [
            //     'name' => 'Facebook Pixel (_fbp)',
            //     'description' => 'Acompanha a eficácia de anúncios exibidos nas redes sociais parceiras e otimiza campanhas de publicidade direcionadas.',
            //     'src' => 'https://connect.facebook.net/en_US/fbevents.js',
            // ]
        ]
    ],
];
