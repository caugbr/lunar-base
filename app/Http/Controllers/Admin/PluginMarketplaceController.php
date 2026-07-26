<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AddonMarketplaceService;
use App\Services\AddonInstallerService;
use Illuminate\Http\Request;

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

        return back()->with('success', $message);
    }

    /**
     * Limpa o cache do catálogo de plugins.
     */
    public function refresh(AddonMarketplaceService $marketplace)
    {
        $marketplace->clearCache();

        return back()->with('success', 'Catálogo de plugins atualizado com sucesso!');
    }
}
