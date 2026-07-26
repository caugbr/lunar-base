<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plugin;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class PluginController extends Controller
{
    /**
     * Display a listing of installed plugins and synchronize them.
     */
    public function index()
    {
        $this->syncPlugins();

        $plugins = Plugin::orderBy('name')->get();

        return view('admin.plugins.index', compact('plugins'));
    }

    /**
     * Toggle the active state of a plugin.
     */
    public function toggle(Plugin $plugin)
    {
        $targetState = ! $plugin->is_active;

        $plugin->update([
            'is_active' => $targetState
        ]);

        $this->setPluginAssetLink($plugin, $targetState);

        $status = $plugin->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Plugin '{$plugin->name}' has been {$status}!");
    }

    /**
     * Toggle the active state of all installed plugins (activate or deactivate all).
     */
    public function toggleAll($status)
    {
        $targetState = (bool) $status;

        $this->syncPlugins();

        $plugins = Plugin::all();

        foreach ($plugins as $plugin) {
            $plugin->update(['is_active' => $targetState]);

            // Força o disco a ficar no estado desejado para cada plugin
            $this->setPluginAssetLink($plugin, $targetState);
        }

        $message = $targetState
            ? 'Todos os plugins instalados foram ativados com sucesso!'
            : 'Todos os plugins instalados foram desativados com sucesso!';

        return back()->with('success', $message);
    }

    /**
     * Define explicitamente o estado do link simbólico no sistema de arquivos.
     *
     * @param Plugin $plugin Instância do Plugin
     * @param bool $enable   TRUE para criar/garantir o link | FALSE para remover o link
     */
    protected function setPluginAssetLink(Plugin $plugin, bool $enable): void
    {
        $pluginIdentifier = Str::kebab($plugin->slug ?? $plugin->name);

        if ($enable) {
            // Garante que o link será criado.
            // O --force apaga qualquer link pré-existente ou quebrado antes de recriar.
            Artisan::call('plugin:link', [
                'plugin' => $pluginIdentifier,
                '--force' => true,
            ]);
        } else {
            // Garante que o link NÃO existirá mais no disco.
            Artisan::call('plugin:link', [
                'plugin' => $pluginIdentifier,
                '--unlink' => true,
            ]);
        }
    }

    /**
     * Lista todos os hooks descobertos no sistema (readonly)
     */
    public function hooks()
    {
        $hooks = [];

        if (class_exists('App\Support\HookDiscoverer')) {
            $discovered = \App\Support\HookDiscoverer::all();

            foreach ($discovered as $hook) {
                $hooks[] = [
                    'name'        => $hook['name'] ?? 'N/A',
                    'type'        => $hook['type'] ?? 'action',
                    'params'      => $hook['params'] ?? '',
                    'description' => $hook['desc'] ?? 'Sem descricao',
                    'file'        => str_replace('\\', '/', $hook['file']),
                ];
            }
        }

        usort($hooks, fn($a, $b) => strcmp($a['name'], $b['name']));

        return view('admin.plugins.hooks', compact('hooks'));
    }

    /**
     * Scan the plugins directory and sync with the database.
     */
    protected function syncPlugins(): void
    {
        $pluginsPath = base_path('plugins');

        if (!File::exists($pluginsPath)) {
            File::makeDirectory($pluginsPath, 0755, true);
            return;
        }

        $directories = File::directories($pluginsPath);
        $scannedFolders = [];

        foreach ($directories as $directory) {
            $folderName = basename($directory);
            $scannedFolders[] = $folderName;
            $manifestPath = $directory . '/plugin.json';

            if (File::exists($manifestPath)) {
                $manifest = json_decode(File::get($manifestPath), true);

                if ($manifest) {
                    Plugin::updateOrCreate(
                        ['folder_name' => $folderName],
                        [
                            'name' => $manifest['name'] ?? $folderName,
                            'service_provider_class' => $manifest['provider'] ?? '',
                            'version' => $manifest['version'] ?? '1.0.0',
                            'description' => $manifest['description'] ?? '',
                        ]
                    );
                }
            }
        }

        // Clean up database records for plugins that no longer exist physically
        Plugin::whereNotIn('folder_name', $scannedFolders)->delete();
    }

    /**
     * Testa se um plugin está ativo
     *
     * @param string $name  Nome do plugin
     * @return boolean
     */
    public function isPluginActive($name)
    {
        return Plugin::where('name', $name)->where('is_active', true)->exists();
    }

    /**
     * Retorna um array com os nomes de todos os plugins ativos
     *
     * @return array
     */
    public function activePlugins(): array
    {
        return Plugin::where('is_active', true)->pluck('name')->toArray();
    }
}
