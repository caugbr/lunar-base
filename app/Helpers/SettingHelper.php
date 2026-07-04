<?php

use App\Models\Setting;
use App\Support\Settings;

if (! function_exists('getSettingsDefinitions')) {
    /**
     * Retorna todas as definições de settings (config + plugins injetados).
     *
     * Ordem de aplicação:
     *   1. Pega o config base
     *   2. Mescla grupos novos registrados via Settings::addGroup()
     *   3. Injeta campos nos grupos via Settings::add()
     */
    function getSettingsDefinitions(): array
    {
        $definitions   = config('settings.definitions', []);
        $injectedGroups = \App\Support\Settings::getInjectedGroups();
        $injectedItems  = \App\Support\Settings::getInjectedItems();

        // 1. Adiciona grupos novos declarados por plugins
        foreach ($injectedGroups as $groupKey => $meta) {
            // Se o grupo já existe no config, o plugin NÃO deve redeclarar
            if (isset($definitions[$groupKey])) {
                \Log::warning("Settings: plugin tentou redeclarar o grupo '{$groupKey}' que já existe no config.");
                continue;
            }
            $definitions[$groupKey] = $meta;
        }

        // 2. Injeta campos nos grupos
        foreach ($injectedItems as $injection) {
            $group = $injection['group'];
            $item  = $injection['item'];
            $after = $injection['after'];

            // Se o grupo não existe nem no config nem foi adicionado, aborta esse item
            if (! isset($definitions[$group])) {
                \Log::warning("Settings: plugin tentou injetar campo no grupo inexistente '{$group}'. Use Settings::addGroup() primeiro.");
                continue;
            }

            if (! isset($definitions[$group]['fields']) || ! is_array($definitions[$group]['fields'])) {
                $definitions[$group]['fields'] = [];
            }

            $fields = $definitions[$group]['fields'];

            // Sem posição definida → vai pro final
            if ($after === null) {
                $fields[] = $item;
            } else {
                $newFields = [];
                $inserted  = false;

                foreach ($fields as $field) {
                    $newFields[] = $field;
                    if (! $inserted && ($field['label'] ?? null) === $after) {
                        $newFields[] = $item;
                        $inserted = true;
                    }
                }

                // Se não achou o label de referência, adiciona no final
                if (! $inserted) {
                    $newFields[] = $item;
                }

                $fields = $newFields;
            }

            $definitions[$group]['fields'] = $fields;
        }

        return $definitions;
    }
}

if (! function_exists('settingDefault')) {
    /**
     * Retorna o valor padrão (default) de uma configuração.
     * Considera tanto o config quanto os items injetados por plugins.
     * Aceita notação de ponto: 'grupo.chave' (ex: 'general.site_name')
     */
    function settingDefault(string $key, $fallback = null)
    {
        if (! str_contains($key, '.')) {
            return $fallback;
        }

        [$groupKey, $fieldKey] = explode('.', $key, 2);

        $definitions = getSettingsDefinitions();
        $fields      = $definitions[$groupKey]['fields'] ?? [];

        foreach ($fields as $field) {
            if (($field['key'] ?? null) === $fieldKey) {
                return $field['default'] ?? $fallback;
            }
        }

        return $fallback;
    }
}

if (! function_exists('setting')) {
    /**
     * Obtém uma configuração específica pelo key usando notação de ponto ('grupo.chave').
     * Se $default for null, adota o valor padrão (config ou plugin).
     */
    function setting($key, $default = null)
    {
        if ($default === null) {
            $default = settingDefault($key);
        }

        return Setting::get($key, $default);
    }
}

if (! function_exists('settingsGroup')) {
    /**
     * Obtém todas as configurações de um grupo.
     * Preenche campos não salvos no banco com seus defaults (config + plugins).
     */
    function settingsGroup($group)
    {
        $definitions = getSettingsDefinitions();
        $fields      = $definitions[$group]['fields'] ?? [];

        // Monta array de defaults
        $defaults = [];
        foreach ($fields as $field) {
            if (isset($field['key'])) {
                $defaults[$field['key']] = $field['default'] ?? null;
            }
        }

        // Busca valores do banco
        $dbSettings = Setting::where('group', $group)
            ->get()
            ->pluck('value', 'key')
            ->toArray();

        return array_merge($defaults, $dbSettings);
    }
}

if (! function_exists('settingsAll')) {
    /**
     * Obtém todas as configurações de todos os grupos.
     * Preenche campos não salvos no banco com seus defaults (config + plugins).
     */
    function settingsAll()
    {
        $definitions = getSettingsDefinitions();

        $defaults = [];
        foreach ($definitions as $group) {
            foreach ($group['fields'] ?? [] as $field) {
                if (isset($field['key'])) {
                    $defaults[$field['key']] = $field['default'] ?? null;
                }
            }
        }

        $dbSettings = Setting::pluck('value', 'key')->toArray();

        return array_merge($defaults, $dbSettings);
    }
}
