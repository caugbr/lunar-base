<?php

use Illuminate\Support\Facades\Crypt;

if (!function_exists('getOption')) {
    function getOption($key, $default = null) {
        $setting = \App\Models\Setting::options()->where('key', $key)->first();
        return $setting ? $setting->typed_value : $default;
    }
}

if (!function_exists('getPrefixedOptions')) {
    function getPrefixedOptions($prefix) {
        return \App\Models\Setting::prefixedOptions($prefix)->get()->mapWithKeys(function ($item) use($prefix) {
            return [str_replace($prefix, '', $item->key) => $item->typed_value];
        });
    }
}

if (!function_exists('setOption')) {
    function setOption($key, $value, $type = 'json') {
        if ($type === 'password' && !empty($value)) {
            $value = Crypt::encryptString($value);
        }

        if ($type === 'json' && is_array($value)) {
            $value = json_encode($value);
        }

        return \App\Models\Setting::updateOrCreate(
            ['group' => '_system_options', 'key' => $key],
            ['value' => $value, 'type' => $type]
        );
    }
}
