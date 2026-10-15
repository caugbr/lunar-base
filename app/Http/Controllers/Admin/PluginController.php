<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plugin;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use App\Services\AddonMarketplaceService;
use Illuminate\Support\Facades\Log;

class PluginController extends Controller
{
    /**
     * Display a listing of installed plugins and synchronize them.
     */
    public function index(AddonMarketplaceService $marketplace)
    {
        $this->syncPlugins();

        $plugins = Plugin::orderBy('name')->get();

        // Busca informações do GitHub para comparar versões na lista local
        $remotePlugins = collect($marketplace->getAvailablePlugins())->keyBy('folder');

        $plugins->transform(function ($plugin) use ($remotePlugins) {
            $folderName = Str::studly($plugin->folder_name);
            $remote     = $remotePlugins->get($folderName);

            $plugin->remote_version = $remote['remote_version'] ?? null;
            $plugin->has_update     = $remote['has_update'] ?? false;
            $plugin->download_url   = $remote['download_url'] ?? null;
            $plugin->changelog      = $remote['changelog'] ?? null;

            // Lê o plugin.json diretamente do disco
            $manifestPath = base_path("plugins/{$plugin->folder_name}/plugin.json");

            // Fallback caso a pasta use StudlyCase
            if (!File::exists($manifestPath)) {
                $manifestPath = base_path("plugins/{$folderName}/plugin.json");
            }

            if (File::exists($manifestPath)) {
                $manifest = json_decode(File::get($manifestPath), true) ?? [];
                $plugin->tags = (array) ($manifest['tags'] ?? []);
            } else {
                $plugin->tags = [];
            }

            return $plugin;
        });

        $tags = config('addons.tags.plugin', []);

        return view('admin.plugins.index', compact('plugins', 'tags'));
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

        $status = $plugin->is_active ? 'ativado' : 'desativado';

        return back()->with('success', "Plugin '{$plugin->name}' foi {$status}!");
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
        $pluginIdentifier = Str::slug($plugin->slug ?? $plugin->name);

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
     * Scan the plugins directory and sync with the database.
     */
    // protected function syncPlugins(): void
    // {
    //     $pluginsPath = base_path('plugins');

    //     if (!File::exists($pluginsPath)) {
    //         File::makeDirectory($pluginsPath, 0755, true);
    //         return;
    //     }

    //     $directories = File::directories($pluginsPath);
    //     $scannedFolders = [];

    //     foreach ($directories as $directory) {
    //         $folderName = basename($directory);
    //         $scannedFolders[] = $folderName;
    //         $manifestPath = $directory . '/plugin.json';

    //         if (File::exists($manifestPath)) {
    //             $manifest = json_decode(File::get($manifestPath), true);

    //             if ($manifest) {
    //                 Plugin::updateOrCreate(
    //                     ['folder_name' => $folderName],
    //                     [
    //                         'name' => $manifest['name'] ?? $folderName,
    //                         'service_provider_class' => $manifest['provider'] ?? '',
    //                         'version' => $manifest['version'] ?? '1.0.0',
    //                         'description' => $manifest['description'] ?? '',
    //                     ]
    //                 );
    //             }
    //         }
    //     }

    //     // Clean up database records for plugins that no longer exist physically
    //     Plugin::whereNotIn('folder_name', $scannedFolders)->delete();
    // }

    /**
     * Scan the plugins directory and sync with the database.
     */
    protected function syncPlugins(): void
    {
        $pluginsPath = base_path('plugins');

        if (!File::exists($pluginsPath)) {
            File::makeDirectory($pluginsPath, 0755, true);
        }

        $directories = File::exists($pluginsPath) ? File::directories($pluginsPath) : [];
        $scannedFolders = [];

        // Processa os plugins válidos existentes no disco
        foreach ($directories as $directory) {
            $folderName = basename($directory);
            $manifestPath = $directory . '/plugin.json';

            if (!File::exists($manifestPath)) {
                continue; // Se não tem plugin.json, não considera plugin válido
            }

            $manifest = json_decode(File::get($manifestPath), true);

            if (!$manifest) {
                continue; // JSON corrompido ou inválido
            }

            $scannedFolders[] = $folderName;

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

        // Busca plugins no banco que não existem mais fisicamente (ou perderam o plugin.json)
        // $missingPlugins = Plugin::whereNotIn('folder_name', $scannedFolders)->get();

        // foreach ($missingPlugins as $plugin) {
        //     // Se o plugin que sumiu estava ativo, limpe seus links simbólicos
        //     if ($plugin->is_active) {
        //         $this->setPluginAssetLink($plugin, false);

        //         Log::warning("Plugin ativo '{$plugin->name}' ({$plugin->folder_name}) foi removido do disco e desativado automaticamente.");
        //     }

        //     // Deleta individualmente para disparar eventos de Model se existirem
        //     $plugin->delete();
        // }

        // Busca os plugins que estão no banco mas não existem mais no disco
        $missingPlugins = Plugin::whereNotIn('folder_name', $scannedFolders)->get();
        $removedActiveNames = [];

        foreach ($missingPlugins as $plugin) {
            if ($plugin->is_active) {
                // Remove os symlinks para não deixar links quebrados na pasta public
                $this->setPluginAssetLink($plugin, false);

                // Guarda o nome para avisar na tela
                $removedActiveNames[] = $plugin->name;

                Log::warning("Plugin ativo '{$plugin->name}' ({$plugin->folder_name}) foi removido do disco.");
            }

            $plugin->delete();
        }

        // =========================================================================
        //INSERIR AQUI:
        // =========================================================================
        if (!empty($removedActiveNames)) {
            $namesList = implode(', ', $removedActiveNames);

            session()->flash(
                'warning',
                "Um ou mais plugins não foram encontrados e precisaram ser desativados (<strong>{$namesList}</strong>)."
            );
        }
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
