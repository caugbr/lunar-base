<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Templates de Páginas Públicas
    |--------------------------------------------------------------------------
    |
    | Define os templates disponíveis para as páginas públicas.
    | A chave é o nome do arquivo (sem .blade.php) e o valor é o rótulo exibido no select.
    |
    */

    'templates' => [
        'page' => 'Default',
        'fullwidth' => 'Fullwidth',
        'left-sidebar' => 'Left Sidebar',
        // 'right-sidebar' => 'Right Sidebar (Barra lateral direita)',
        // 'hero' => 'Hero (Banner no topo)',
    ],

    /*
    |--------------------------------------------------------------------------
    | Template Padrão
    |--------------------------------------------------------------------------
    */

    'default' => 'page',
];
