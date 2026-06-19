<?php

use App\Models\Setting;

if (!function_exists('settingDefault')) {
    /**
     * Retorna o valor padrão (default) de uma configuração definida em config/settings.php
     * Aceita notação de ponto: 'grupo.chave' (ex: 'general.site_name')
     */
    function settingDefault(string $key, $fallback = null)
    {
        if (!str_contains($key, '.')) {
            return $fallback;
        }

        [$groupKey, $fieldKey] = explode('.', $key, 2);
        $definitions = config("settings.definitions.{$groupKey}.fields", []);

        foreach ($definitions as $field) {
            if (($field['key'] ?? null) === $fieldKey) {
                return $field['default'] ?? $fallback;
            }
        }

        return $fallback;
    }
}

if (!function_exists('setting')) {
    /**
     * Obtém uma configuração específica pelo key usando notação de ponto ('grupo.chave').
     * Se $default for null, adota o valor padrão definido em config/settings.php.
     */
    function setting($key, $default = null)
    {
        if ($default === null) {
            $default = settingDefault($key);
        }

        // Para a busca no banco, separamos o grupo para filtrar corretamente na query
        if (str_contains($key, '.')) {
            [$groupKey, $fieldKey] = explode('.', $key, 2);

            $dbSetting = Setting::where('group', $groupKey)
                ->where('key', $fieldKey)
                ->first();

            return $dbSetting ? $dbSetting->value : $default;
        }

        return Setting::get($key, $default);
    }
}

if (!function_exists('settingsGroup')) {
    /**
     * Obtém todas as configurações de um grupo.
     * Preenche campos não salvos no banco com seus defaults do config.
     */
    function settingsGroup($group)
    {
        $definitions = config('settings.definitions.' . $group . '.fields', []);

        // Monta array de defaults do config para este grupo
        $defaults = [];
        foreach ($definitions as $field) {
            $defaults[$field['key']] = $field['default'] ?? null;
        }

        // Busca valores do banco filtrados pelo escopo do grupo
        $dbSettings = Setting::where('group', $group)->get()->pluck('value', 'key')->toArray();

        // Mescla: banco tem prioridade, config preenche o que falta
        return array_merge($defaults, $dbSettings);
    }
}

if (!function_exists('settingsAll')) {
    /**
     * Obtém todas as configurações de todos os grupos.
     * Preenche campos não salvos no banco com seus defaults do config.
     */
    function settingsAll()
    {
        $definitions = config('settings.definitions', []);

        // Monta todos os defaults do config
        $defaults = [];
        foreach ($definitions as $groupKey => $group) {
            foreach ($group['fields'] ?? [] as $field) {
                $defaults[$field['key']] = $field['default'] ?? null;
            }
        }

        // Busca todos os valores do banco
        $dbSettings = Setting::pluck('value', 'key')->toArray();

        // Mescla: banco tem prioridade, config preenche o que falta
        return array_merge($defaults, $dbSettings);
    }
}
