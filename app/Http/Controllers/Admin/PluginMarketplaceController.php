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
     * Remove completamente um único plugin do disco e do banco.
     */
    public function remove(string $folderName, Request $request, AddonMarketplaceService $marketplace)
    {
        $plugin = Plugin::where('folder_name', $folderName)->first();
        $displayName = $plugin->name ?? $folderName;
        $shouldRollback = $request->boolean('rollback');

        // Executa a destruição completa encapsulada
        if (! $this->deletePlugin($folderName, $shouldRollback)) {
            return back()->with('error', 'Não foi possível remover o plugin.');
        }

        $marketplace->clearCache();

        return redirect()->action([PluginController::class, 'index'])
            ->with('success', "Plugin '{$displayName}' foi removido com sucesso!");
    }

    /**
     * Remove todos os plugins inativos em lote (apenas os disponíveis no catálogo).
     */
    public function removeInactive(Request $request, AddonMarketplaceService $marketplace)
    {
        // Mapeia os plugins remotos que realmente podem ser baixados novamente
        $downloadablePlugins = collect($marketplace->getAvailablePlugins())
            ->filter(fn ($item) => ! empty($item['download_url']))
            ->keyBy(fn ($item) => str_replace(' ', '', $item['folder'] ?? $item['name'] ?? ''));

        // Busca todos os plugins inativos no banco
        $inactivePlugins = Plugin::where('is_active', false)->get();
        $rollbackFolders = (array) $request->input('rollback_folders', []);

        if ($inactivePlugins->isEmpty()) {
            return back()->with('info', 'Nenhum plugin inativo encontrado para remoção.');
        }

        $deletedCount  = 0;
        $skippedCustom = 0;

        foreach ($inactivePlugins as $plugin) {
            $folderName = $plugin->folder_name;

            // Trava de segurança: se não está no catálogo ou não tem download_url, é próprio/local. Não toque!
            if (! $downloadablePlugins->has($folderName)) {
                $skippedCustom++;
                continue;
            }

            if ($this->deletePlugin($plugin->folder_name, in_array($plugin->folder_name, $rollbackFolders))) {
                $deletedCount++;
            }
        }

        // Limpa o cache do catálogo apenas se algo foi deletado
        if ($deletedCount > 0) {
            $marketplace->clearCache();
        }

        if ($deletedCount === 0 && $skippedCustom > 0) {
            return back()->with('info', 'Nenhum plugin foi excluído. Os plugins inativos presentes são locais/próprios e foram preservados.');
        }

        $message = "{$deletedCount} plugin(s) inativo(s) excluído(s) com sucesso!";
        if ($skippedCustom > 0) {
            $message .= " ({$skippedCustom} plugin(s) próprio(s) preservado(s)).";
        }

        return back()->with('success', $message);
    }

    /**
     * Executa a limpeza física da pasta, link simbólico e registro no banco de dados.
     */
    protected function deletePlugin(string $folderName, bool $rollback = false): bool
    {
        if (empty($folderName)) {
            return false;
        }

        $shouldRemove = applyFilters('should_remove_plugin', true, $folderName);
        if (!$shouldRemove) {
            return false;
        }

        $pluginPath = base_path("plugins/{$folderName}");

        // ROLLBACK PRIMEIRO: precisa dos arquivos físicos ainda presentes no disco
        if ($rollback && Plugin::checkMigrations($folderName)) {
            Artisan::call('migrate:rollback', [
                '--path'  => "plugins/{$folderName}/database/migrations",
                '--force' => true,
            ]);
        }

        // Apaga a pasta física do plugin
        if (File::exists($pluginPath)) {
            File::deleteDirectory($pluginPath);
        }

        // Apaga o link simbólico de assets públicos
        Artisan::call('plugin:link', [
            'plugin'   => Str::kebab($folderName),
            '--unlink' => true,
        ]);

        // Apaga o registro do banco de dados (se cadastrado)
        Plugin::where('folder_name', $folderName)->delete();

        doAction('plugin_removed', $folderName);

        return true;
    }
}
