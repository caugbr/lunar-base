<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class SettingController extends Controller
{
    public function index()
    {
        $groups = config('settings.definitions', []);

        foreach ($groups as $groupKey => &$group) {
            if (!isset($group['fields']) || !is_array($group['fields'])) {
                continue;
            }

            foreach ($group['fields'] as &$field) {
                $key = $field['key'];
                $saved = Setting::where('key', $key)->first();
                $value = $saved ? $saved->typed_value : ($field['default'] ?? null);

                if (($field['type'] ?? '') === 'image' && $value) {
                    $field['value'] = getImage($value);
                    $field['path'] = $value;
                } else {
                    $field['value'] = $value;
                }
            }
        }

        return view('admin.settings.index', compact('groups'));
    }

    public function update(Request $request)
    {
        $definitions = config('settings.definitions', []);

        // === INÍCIO DA ADIÇÃO: VALIDAÇÃO DINÂMICA ===
        $rules = [];
        $messages = [];

        foreach ($definitions as $groupKey => $group) {
            foreach ($group['fields'] ?? [] as $def) {
                $key = $def['key'];
                $fieldRules = ['nullable'];

                if (isset($def['forbidden']) && is_array($def['forbidden'])) {
                    $resolvedForbidden = [];

                    foreach ($def['forbidden'] as $word) {
                        // Se apontar para outra configuração, resolve o valor dinamicamente
                        if (str_starts_with($word, 'setting:')) {
                            $settingKey = str_replace('setting:', '', $word);
                            $resolvedForbidden[] = setting($settingKey);
                        } else {
                            $resolvedForbidden[] = $word;
                        }
                    }

                    // Remove valores vazios e junta na regra not_in do Laravel
                    $resolvedForbidden = array_filter($resolvedForbidden);
                    if (!empty($resolvedForbidden)) {
                        $fieldRules[] = 'not_in:' . implode(',', $resolvedForbidden);
                        $messages["{$key}.not_in"] = "O valor informado no campo \"{$def['label']}\" é uma palavra reservada ou já está em uso por outro namespace.";
                    }
                }

                $rules[$key] = $fieldRules;
            }
        }

        // Executa a validação antes de começar a salvar
        $request->validate($rules, $messages);
        // === FIM DA ADIÇÃO: VALIDAÇÃO DINÂMICA ===

        foreach ($definitions as $groupKey => $group) {
            if (!isset($group['fields']) || !is_array($group['fields'])) {
                continue;
            }

            foreach ($group['fields'] as $def) {
                $key = $def['key'];
                $type = $def['type'] ?? 'text';

                // === PASSWORD: criptografa antes de salvar ===
                if ($type === 'password') {
                    $input = $request->input($key);

                    // Se o campo veio vazio, mantém o valor existente
                    if (blank($input)) {
                        $existing = Setting::where('key', $key)->value('value');
                        if ($existing) {
                            Setting::set($key, $existing, $groupKey, $type);
                        }
                        continue;
                    }

                    $encrypted = Crypt::encryptString($input);
                    Setting::set($key, $encrypted, $groupKey, $type);
                    continue;
                }

                // === IMAGE ===
                if ($type === 'image') {
                    $shouldRemove = $request->boolean("remove_settings.{$key}");

                    if ($shouldRemove) {
                        $oldSetting = Setting::where('key', $key)->first();
                        if ($oldSetting && $oldSetting->value) {
                            deleteImage($oldSetting->value, 'settings');
                        }
                        $value = null;

                    } elseif ($request->hasFile($key)) {
                        $request->validate([
                            $key => 'file|mimetypes:image/jpeg,image/png,image/gif,image/webp,image/svg+xml|max:5120'
                        ]);

                        $result = uploadImage($request->file($key), 'settings', [
                            'save_original' => true,
                            'create_media_record' => false,
                            'thumb' => null,
                            'resize' => null,
                        ]);

                        $value = $result['original'];

                        $oldSetting = Setting::where('key', $key)->first();
                        if ($oldSetting && $oldSetting->value) {
                            deleteImage($oldSetting->value, 'settings');
                        }

                    } else {
                        $value = $request->input($key . '_current');
                    }

                    Setting::set($key, $value, $groupKey, $type);
                    continue;
                }

                // === RADIO / SELECT ===
                if (in_array($type, ['radio', 'select'])) {
                    $value = $request->input($key, $def['default'] ?? '');
                    Setting::set($key, $value, $groupKey, $type);
                    continue;
                }

                // === CHECKBOX com array ===
                if ($type === 'checkbox' && !empty($def['options'])) {
                    $value = $request->input($key, []);
                    $value = is_array($value) ? implode(',', $value) : $value;
                    Setting::set($key, $value, $groupKey, $type);
                    continue;
                }

                // === CHECKBOX único / SWITCH ===
                if ($type === 'checkbox' || $type === 'switch') {
                    $value = $request->boolean($key);
                    Setting::set($key, $value, $groupKey, $type);
                    continue;
                }

                // === NUMBER ===
                if ($type === 'number') {
                    $value = $request->input($key, $def['default'] ?? 0);
                    Setting::set($key, $value, $groupKey, $type);
                    continue;
                }

                // === DEFAULT: text, textarea, url, email ===
                $value = $request->input($key, $def['default'] ?? '');
                Setting::set($key, $value, $groupKey, $type);
            }
        }

        return redirect()->route('admin.settings.index')
            ->with('success', 'Configurações salvas com sucesso!');
    }
}
