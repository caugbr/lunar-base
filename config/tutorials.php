<?php
// Usado pelo comando tutorials:update para atualizar
// os tutoriais com versão e data a cada mudança

$meses = [
    'January'   => 'Janeiro',
    'February'  => 'Fevereiro',
    'March'     => 'Março',
    'April'     => 'Abril',
    'May'       => 'Maio',
    'June'      => 'Junho',
    'July'      => 'Julho',
    'August'    => 'Agosto',
    'September' => 'Setembro',
    'October'   => 'Outubro',
    'November'  => 'Novembro',
    'December'  => 'Dezembro',
];
$nameVersion = config('app.name', 'Lunar Base') . ' v' . config('app.version', '1.0.0');
$footerText = 'Última atualização: ' . $meses[date('F')] . '/' . date('Y') .
              ' | &copy; ' . date('Y') . ' ' . config('app.author');

return [
    /*
    |--------------------------------------------------------------------------
    | Diretório alvo
    |--------------------------------------------------------------------------
    */
    'directory' => public_path('tutorials'),

    /*
    |--------------------------------------------------------------------------
    | Regras de Substituição (Regex)
    |--------------------------------------------------------------------------
    | 'pattern': A expressão regular com grupos de captura ().
    | 'replace': A string de substituição usando $1, $2, etc., + o valor dinâmico.
    */
    'replacements' => [
        [
            'pattern' => '#(- )[^<]+(</title>)#',
            'replace' => '$1' . $nameVersion . '$2',
        ],
        [
            'pattern' => '#(<p data-subtitle>)[^-]+( -)#',
            'replace' => '$1' . $nameVersion . '$2',
        ],
        [
            'pattern' => '#(- )[^<]+(</strong></p>)#',
            'replace' => '$1' . $nameVersion . '$2',
        ],
        [
            'pattern' => '#(<p data-footer-info>)[^<]+(</p>)#',
            'replace' => '$1' . $footerText . '$2',
        ]
    ],
];
