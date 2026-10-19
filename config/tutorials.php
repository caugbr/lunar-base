<?php
/**
 * =============================================================================
 * LUNAR BASE - ARQUIVO ESTRUTURAL DO CORE
 * =============================================================================
 *
 * ATENÇÃO: NÃO EDITE ESTE ARQUIVO DIRETAMENTE!
 *
 * Este arquivo é parte integrante do núcleo do sistema e define esquemas,
 * regras e metadados oficiais da plataforma.
 *
 * Qualquer alteração manual feita aqui SERÁ SOBRESCRITA a cada atualização
 * de versão baixada pelo CoreUpdateService.
 *
 * Para personalizações de projeto:
 * - Valores de configuração: altere pelo Painel Administrativo ou banco de dados.
 * - Variáveis de ambiente: utilize o arquivo .env.
 * - Regras complementares: utilize os arquivos de projeto correspondentes.
 *
 * =============================================================================
 */

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
$nameVersion = config('app.name') . ' v' . config('app.version');
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
            'pattern' => '#(<span class="app-name">)[^<]+(</span>)#',
            'replace' => '$1' . config('app.name') . '$2',
        ],
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
