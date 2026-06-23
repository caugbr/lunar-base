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
        $originalValues = settingsAll();

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

        $this->logChanges($originalValues, $request->all());

        foreach ($definitions as $groupKey => $group) {
            if (!isset($group['fields']) || !is_array($group['fields'])) {
                continue;
            }

            foreach ($group['fields'] as $def) {
                $key = $def['key'];
                $type = $def['type'] ?? 'text';

                // === PASSWORD: criptografa antes de salvar ===
                if ($type === 'password') {
                    // 1. Verifica se marcou para remover
                    if ($request->boolean("remove_settings.{$key}")) {
                        Setting::set($key, '', $groupKey, $type);
                        continue;
                    }

                    $input = $request->input($key);

                    // 2. Se o campo veio vazio, mantém o valor existente
                    if (blank($input)) {
                        $existing = Setting::where('key', $key)->value('value');
                        if ($existing) {
                            Setting::set($key, $existing, $groupKey, $type);
                        }
                        continue;
                    }

                    // 3. Nova senha informada — criptografa e salva
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

    private function logChanges(array $original, array $modified): void
    {
        // Campos internos do Laravel e de controle da interface
        $ignoredKeys = ['_token', '_active_tab', '_method'];

        // Tipos de campo que nunca devem ter valores logados
        $sensitiveTypes = ['password'];

        // Pega as definições para saber os tipos dos campos
        $definitions = config('settings.definitions', []);
        $fieldTypes = [];

        foreach ($definitions as $group) {
            foreach ($group['fields'] ?? [] as $field) {
                $fieldTypes[$field['key']] = $field['type'] ?? 'text';
            }
        }

        $changes = [];

        foreach ($modified as $key => $newValue) {
            // Ignora campos internos do Laravel
            if (in_array($key, $ignoredKeys, true)) {
                continue;
            }

            // Ignora campos auxiliares (ex: site_thumbnail_current, remove_settings.*)
            if (str_ends_with($key, '_current') || str_starts_with($key, 'remove_settings.')) {
                continue;
            }

            // Normaliza a chave: remove sufixo _current se existir no original
            $originalKey = $key;

            // Pega valor original (pode ser null se campo não existia)
            $oldValue = $original[$originalKey] ?? null;

            // Se não mudou, pula
            if ($oldValue === $newValue || $newValue === NULL) {
                continue;
            }

            // Se é campo de senha, loga apenas que foi alterado, sem o valor
            $fieldType = $fieldTypes[$originalKey] ?? 'text';

            if (in_array($fieldType, $sensitiveTypes, true)) {
                $changes[$originalKey] = [
                    'old' => '[SENSÍVEL]',
                    'new' => '[SENSÍVEL]',
                ];
            } else {
                $changes[$originalKey] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        if (empty($changes)) {
            return;
        }

        log_admin(
            'Configurações do sistema atualizadas',
            'settings',
            [
                'modified_keys' => array_keys($changes),
                'changes' => $changes,
            ]
        );
    }
}
