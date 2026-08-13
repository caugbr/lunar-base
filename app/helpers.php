<?php

/**
 * Carregador Mestre de Helpers do Core do Lunar Base.
 * Varre e carrega automaticamente qualquer arquivo dentro de app/Helpers/*.php
 */
$helpersDir = __DIR__ . '/Helpers';

if (is_dir($helpersDir)) {
    foreach (glob($helpersDir . '/*.php') as $helperFile) {
        require_once $helperFile;
    }
}
