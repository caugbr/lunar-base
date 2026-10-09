<?php

/**
 * Carregador Mestre de Helpers do Core, Temas e Plugins do Lunar Base.
 */

// Carrega automaticamente todos os helpers do Core (app/Helpers/*.php)
$coreHelpersDir = __DIR__ . '/Helpers';

foreach (glob($coreHelpersDir . '/*.php') as $helperFile) {
    require_once $helperFile;
}
