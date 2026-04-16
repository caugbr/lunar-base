<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        // Pega a estrutura hierárquica do config
        $groups = config('settings.definitions', []);

        // Itera sobre cada grupo e seus campos para injetar os valores salvos
        foreach ($groups as $groupKey => &$group) {
            if (!isset($group['fields']) || !is_array($group['fields'])) {
                continue;
            }

            foreach ($group['fields'] as &$field) {
                $key = $field['key'];
                $saved = Setting::where('key', $key)->first();
                $value = $saved ? $saved->typed_value : ($field['default'] ?? null);

                // 🔁 Usa o helper para imagens: path → URL
                if (($field['type'] ?? '') === 'image' && $value) {
                    $field['value'] = getImage($value);
                    $field['path'] = $value; // Mantém o path original para delete
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

        foreach ($definitions as $groupKey => $group) {
            // Pula se não tiver campos (defesa)
            if (!isset($group['fields']) || !is_array($group['fields'])) {
                continue;
            }

            foreach ($group['fields'] as $def) {
                $key = $def['key'];
                $type = $def['type'] ?? 'text';

                // Tratamento especial para imagens
                if ($type === 'image') {
                    $shouldRemove = $request->boolean("remove_settings.{$key}");

                    if ($shouldRemove) {
                        // Deleta arquivos físicos
                        $oldSetting = Setting::where('key', $key)->first();
                        if ($oldSetting && $oldSetting->value) {
                            deleteImage($oldSetting->value, 'settings');
                        }
                        $value = null;

                    } elseif ($request->hasFile($key)) {
                        // 🟡 Novo upload
                        $request->validate([
                            $key => 'image|max:5120'
                        ]);

                        $result = uploadImage($request->file($key), 'settings', [
                            'save_original' => true,
                            'create_media_record' => false,
                            'thumb' => null,
                            'resize' => null,
                        ]);

                        $value = $result['original'];

                        // Deleta a imagem antiga se existir
                        $oldSetting = Setting::where('key', $key)->first();
                        if ($oldSetting && $oldSetting->value) {
                            deleteImage($oldSetting->value, 'settings');
                        }

                    } else {
                        // 🟢 Mantém a imagem atual (fallback)
                        $value = $request->input($key . '_current');
                    }

                    Setting::set($key, $value, $groupKey, $type);

                }
                // Tratamento para radio
                elseif ($type === 'radio') {
                    $value = $request->input($key, $def['default'] ?? '');
                    Setting::set($key, $value, $groupKey, $type);
                }
                // Tratamento para select
                elseif ($type === 'select') {
                    $value = $request->input($key, $def['default'] ?? '');
                    Setting::set($key, $value, $groupKey, $type);
                }
                // Tratamento para checkboxes com array
                elseif ($type === 'checkbox' && !empty($def['options'])) {
                    $value = $request->input($key, []);
                    if (is_array($value)) {
                        $value = implode(',', $value);
                    }
                    Setting::set($key, $value, $groupKey, $type);
                }
                // Tratamento para checkbox único
                elseif ($type === 'checkbox' && empty($def['options'])) {
                    $value = $request->boolean($key);
                    Setting::set($key, $value, $groupKey, $type);
                }
                // Tratamento para number
                elseif ($type === 'number') {
                    $value = $request->input($key, $def['default'] ?? 0);
                    Setting::set($key, $value, $groupKey, $type);
                }
                // Tratamento para os demais tipos (text, textarea, url, email)
                else {
                    $value = $request->input($key, $def['default'] ?? '');
                    Setting::set($key, $value, $groupKey, $type);
                }
            }
        }

        return redirect()->route('admin.settings.index')
            ->with('success', 'Configurações salvas com sucesso!');
    }
}
