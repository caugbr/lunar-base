<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Plugin;
use Illuminate\Support\Facades\File;

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
        $plugin->update([
            'is_active' => !$plugin->is_active
        ]);

        $status = $plugin->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Plugin '{$plugin->name}' has been {$status}!");
    }

    /**
     * Toggle the active state of all installed plugins (activate or deactivate all).
     */
    public function toggleAll($status)
    {
        // 1. Converte o parâmetro de texto para booleano puro do PHP (0 vira false, 1 vira true)
        $isActive = (bool) $status;

        // 2. Sincroniza a pasta física primeiro
        $this->syncPlugins();

        // 3. Atualiza todos os registros de uma só vez de forma otimizada
        Plugin::query()->update(['is_active' => $isActive]);

        $message = $isActive
            ? 'Todos os plugins instalados foram ativados com sucesso!'
            : 'Todos os plugins instalados foram desativados com sucesso!';

        return back()->with('success', $message);
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
}
