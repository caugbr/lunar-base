<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Regras de tradução para arquivos em /config
    |--------------------------------------------------------------------------
    |
    | Caminhos com dot-notation e curingas (*) que apontam para
    | textos humanos que devem ser traduzidos em tempo de execução.
    */
    'rules' => [
        // addons
        'addons.tags.*.*.name',
        'addons.tags.*.*.description',

        // admin
        'admin.menu.*.title',
        'admin.menu.*.items.label',
        'admin.dashboard.title',
        'admin.dashboard.subtitle',
        'admin.dashboard.cardTitle',

        // rolesPermissions
        'rolesPermissions.roles.*.name',
        'rolesPermissions.roles.*.description',
        'rolesPermissions.permissionGroups.*.*',

        // settings
        'settings.definitions.*.tab',
        'settings.definitions.*.title',
        'settings.definitions.*.description',
        'settings.definitions.*.fields.*.label',
        'settings.definitions.*.fields.*.description',
        'settings.definitions.*.fields.*.options.*',

        // site
        'site.mainMenu.*.label',
        'site.loginFooterText',
    ],
];
