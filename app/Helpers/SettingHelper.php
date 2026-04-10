<?php

use App\Models\Setting;

if (!function_exists('setting')) {
    /**
     * Obtém uma configuração específica pelo key
     */
    function setting($key, $default = null)
    {
        return Setting::get($key, $default);
    }
}

if (!function_exists('settingsGroup')) {
    /**
     * Obtém todas as configurações de um grupo
     */
    function settingsGroup($group)
    {
        $settings = Setting::where('group', $group)->get();
        return $settings->pluck('value', 'key')->toArray();
    }
}

if (!function_exists('settingsAll')) {
    /**
     * Obtém todas as configurações
     */
    function settingsAll()
    {
        return Setting::pluck('value', 'key')->toArray();
    }
}
