<?php

/**
 * Carregador Mestre de Helpers do Core, Temas e Plugins do Lunar Base.
 */

// Carrega automaticamente todos os helpers do Core (app/Helpers/*.php)
$coreHelpersDir = __DIR__ . '/Helpers';

foreach (glob($coreHelpersDir . '/*.php') as $helperFile) {
    require_once $helperFile;
}

// Carrega automaticamente os helpers de todos os temas e plugins
$projectRoot = dirname(__DIR__);

$pluginsHelpers = glob($projectRoot . '/plugins/*/HelperFunctions/*.php') ?: [];
$themesHelpers  = glob($projectRoot . '/themes/*/HelperFunctions/*.php') ?: [];
$helpers        = array_merge($pluginsHelpers, $themesHelpers);

foreach ($helpers as $helperFile) {
    $info = addon_info($helperFile);

    // Só dá require se o plugin ou tema estiver de fato ATIVO no banco!
    if ($info['type'] === 'plugin' && ! is_plugin_active($info['folder_name'])) {
        continue;
    }

    if ($info['type'] === 'theme' && ! is_theme_active($info['folder_name'])) {
        continue;
    }

    if (is_file($helperFile)) {
        require_once $helperFile;
    }
}
