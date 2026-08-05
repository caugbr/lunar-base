<?php

namespace App\Http\Controllers\Admin;

use App\Models\Plugin;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\PluginController;
use App\Services\AddonMarketplaceService;
use App\Services\AddonInstallerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PluginMarketplaceController extends Controller
{
    /**
     * Exibe o Marketplace de Plugins.
     */
    public function index(AddonMarketplaceService $marketplace)
    {
        $plugins = $marketplace->getAvailablePlugins();

        return view('admin.plugins.marketplace', compact('plugins'));
    }

    /**
     * Instala múltiplos plugins selecionados na view em lote.
     */
    public function installBatch(Request $request, AddonInstallerService $installer)
    {
        $selectedPlugins = $request->input('selected_plugins', []);

        if (empty($selectedPlugins)) {
            return back()->with('error', 'Nenhum plugin foi selecionado para instalação.');
        }

        $installedCount = 0;
        $failedPlugins  = [];

        foreach ($selectedPlugins as $addon) {
            $data = is_string($addon) ? json_decode($addon, true) : $addon;

            $name        = $data['name'] ?? null;
            $downloadUrl = $data['download_url'] ?? null;

            if (! $name || ! $downloadUrl) {
                continue;
            }

            // Instala como tipo 'plugin'
            $success = $installer->installFromUrl($name, $downloadUrl, 'plugin');

            if ($success) {
                $installedCount++;
            } else {
                $failedPlugins[] = $name;
            }
        }

        // Sincroniza plugins no banco de dados
        if (method_exists($this, 'syncPlugins')) {
            $this->syncPlugins();
        }

        if ($installedCount === 0) {
            return back()->with('error', 'Falha ao instalar os plugins selecionados.');
        }

        $message = "{$installedCount} plugin(s) instalado(s) com sucesso!";
        if (count($failedPlugins) > 0) {
            $message .= " Falha em: " . implode(', ', $failedPlugins);
        }

        return redirect()->action([PluginController::class, 'index'])->with('success', $message);
    }

    /**
     * Limpa o cache do catálogo de plugins.
     */
    public function refresh(AddonMarketplaceService $marketplace)
    {
        $marketplace->clearCache();

        return back()->with('success', 'Catálogo de plugins atualizado com sucesso!');
    }

    /**
     * Remove completamente a pasta do plugin do disco e do banco.
     */
    public function remove(string $folder, AddonMarketplaceService $marketplace)
    {
        // 1. Sanitiza o nome da pasta em StudlyCase por segurança
        $folderName = Str::studly($folder);

        if (empty($folderName)) {
            return back()->with('error', 'Nome de plugin inválido.');
        }

        $pluginPath = base_path("plugins/{$folderName}");

        // 2. Apaga a pasta física do plugin
        if (File::exists($pluginPath)) {
            File::deleteDirectory($pluginPath);
        }

        // 3. Apaga o link simbólico de public/plugins/{kebab}
        Artisan::call('plugin:link', [
            'plugin' => Str::kebab($folderName),
            '--unlink' => true,
        ]);

        // 4. Apaga o registro do banco de dados (se cadastrado)
        Plugin::where('folder_name', $folderName)->delete();

        // 5. Limpa o cache do catálogo do marketplace para recalcular os status
        $marketplace->clearCache();

        return redirect()->action([PluginController::class, 'index'])->with('success', "Plugin '{$folderName}' foi removido com sucesso!");
        return back()->with('success', "Plugin '{$folderName}' foi removido com sucesso!");
    }
}
