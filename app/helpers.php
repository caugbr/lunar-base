<?php

/**
 * Carregador Mestre de Helpers do Core e Plugins do Lunar Base.
 */

// Carrega automaticamente todos os helpers do Core (app/Helpers/*.php)
$coreHelpersDir = __DIR__ . '/Helpers';

foreach (glob($coreHelpersDir . '/*.php') as $helperFile) {
    require_once $helperFile;
}

// Carrega automaticamente os helpers de todos os plugins (/plugins/*/Helpers/*.php)
$projectRoot = dirname(__DIR__);
$pluginsHelpersPattern = $projectRoot . '/plugins/*/HelperFunctions/*.php';

foreach (glob($pluginsHelpersPattern) as $helperFile) {
    require_once $helperFile;
}
