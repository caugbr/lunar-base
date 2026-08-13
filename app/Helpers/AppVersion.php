<?php

if (! function_exists('appVersion')) {
    /**
     * Retorna a versão atual do Core do Lunar Base.
     */
    function appVersion(): string
    {
        // Cache estático na memória RAM durante a requisição (evita ler o disco várias vezes na mesma página)
        static $cachedVersion = null;

        if ($cachedVersion === null) {
            $versionFile = base_path('VERSION');

            if (file_exists($versionFile)) {
                $cachedVersion = trim(file_get_contents($versionFile));
            } else {
                $cachedVersion = '1.12.0';
            }
        }

        return $cachedVersion ?: '1.12.0';
    }
}
