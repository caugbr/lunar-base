<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $definitions = config('settings.definitions', []);

        foreach ($definitions as &$def) {
            $saved = Setting::where('key', $def['key'])->first();
            $value = $saved ? $saved->typed_value : ($def['default'] ?? null);

            // 🔁 Usa o helper para imagens: path → URL
            if ($def['type'] === 'image' && $value) {
                $def['value'] = getImage($value); // 👈 Simples e limpo
                $def['path'] = $value; // Mantém o path original se precisar para delete
            } else {
                $def['value'] = $value;
            }
        }

        $groups = collect($definitions)->groupBy('group');

        return view('admin.settings.index', compact('groups'));
    }

    public function update(Request $request)
    {
        $definitions = config('settings.definitions', []);

        foreach ($definitions as $def) {
            $key = $def['key'];
            $type = $def['type'];

            // Tratamento especial para imagens
            // if ($type === 'image') {
            //     if ($request->hasFile($key)) {
            //         $result = uploadImage($request->file($key), 'settings', [
            //             'save_original' => true,
            //             'thumb' => null,
            //             'resize' => null,
            //         ]);

            //         $value = $result['original'];

            //         // Remove imagem antiga
            //         $oldSetting = Setting::where('key', $key)->first();
            //         if ($oldSetting && $oldSetting->value) {
            //             deleteImage($oldSetting->value, 'settings');
            //         }
            //     } else {
            //         $value = $request->input($key . '_current');
            //     }
            // }
            // Dentro do foreach, no tratamento de 'image':
            if ($type === 'image') {

                // 🔴 Verifica se pediu para remover
                $shouldRemove = $request->boolean("remove_settings.{$key}");

                if ($shouldRemove) {
                    // Deleta arquivos físicos
                    $oldSetting = Setting::where('key', $key)->first();
                    if ($oldSetting && $oldSetting->value) {
                        deleteImage($oldSetting->value, 'settings');
                    }
                    $value = null; // Salva null no banco

                } elseif ($request->hasFile($key)) {
                    // 🟡 Novo upload
                    $request->validate([
                        $key => 'image|max:5120' // opcional: valida tipo e tamanho
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

                Setting::set($key, $value, $def['group'], $type);
            }
            // Tratamento para radio
            elseif ($type === 'radio') {
                $value = $request->input($key, $def['default'] ?? '');
            }
            // Tratamento para select
            elseif ($type === 'select') {
                $value = $request->input($key, $def['default'] ?? '');
            }
            // Tratamento para checkboxes com array
            elseif ($type === 'checkbox' && !empty($def['options'])) {
                $value = $request->input($key, []);
                if (is_array($value)) {
                    $value = implode(',', $value);
                }
            }
            // Tratamento para checkbox único
            elseif ($type === 'checkbox' && empty($def['options'])) {
                $value = $request->boolean($key);
            }
            // Tratamento para number
            elseif ($type === 'number') {
                $value = $request->input($key, $def['default'] ?? 0);
            }
            // Tratamento para os demais tipos (text, textarea, url, email)
            else {
                $value = $request->input($key, $def['default'] ?? '');
            }

            Setting::set($key, $value, $def['group'], $type);
        }

        return redirect()->route('admin.settings.index')
            ->with('success', 'Configurações salvas com sucesso!');
    }
}
