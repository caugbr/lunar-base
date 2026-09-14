<?php

namespace App\Services;

use App\Models\Plugin;
use App\Models\Theme;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class AddonDependencyService
{
    protected AddonMarketplaceService $marketplace;

    public function __construct(AddonMarketplaceService $marketplace)
    {
        $this->marketplace = $marketplace;
    }

    /**
     * Retorna a lista consolidada de dependências pendentes
     */
    public function getUnresolvedDependencies(): array
    {
        $unresolved = [];

        // 1. Checa as dependências do tema ativo
        $activeTheme = Theme::where('is_active', true)->first();
        if ($activeTheme) {
            $unresolved = array_merge($unresolved, $this->checkThemeDependencies($activeTheme));
        }

        // 2. Checa as dependências dos plugins ativos
        $activePlugins = Plugin::where('is_active', true)->get();
        foreach ($activePlugins as $plugin) {
            $unresolved = array_merge($unresolved, $this->checkPluginDependencies($plugin));
        }

        // 3. Checa as dependências globais do projeto em config/addons.php
        $unresolved = array_merge($unresolved, $this->checkProjectRequirements());

        return $unresolved;
    }

    /**
     * Valida as dependências declaradas no theme.json
     */
    protected function checkThemeDependencies(Theme $theme): array
    {
        $manifestPath = base_path("themes/{$theme->folder_name}/theme.json");
        if (!File::exists($manifestPath)) {
            return [];
        }

        $manifest = json_decode(File::get($manifestPath), true) ?? [];
        $dependencies = $manifest['dependencies'] ?? [];

        return $this->resolveDependencyList($dependencies, 'tema', $theme->name);
    }

    /**
     * Valida as dependências declaradas no plugin.json
     */
    protected function checkPluginDependencies(Plugin $plugin): array
    {
        $manifestPath = base_path("plugins/{$plugin->folder_name}/plugin.json");
        if (!File::exists($manifestPath)) {
            return [];
        }

        $manifest = json_decode(File::get($manifestPath), true) ?? [];
        $dependencies = $manifest['dependencies'] ?? [];

        return $this->resolveDependencyList($dependencies, 'plugin', $plugin->name);
    }

    /**
     * Valida as dependências declaradas no config/addons.php
     */
    protected function checkProjectRequirements(): array
    {
        $required = config('addons.required', []);
        return $this->resolveDependencyList($required, 'projeto', 'Sistema');
    }

    /**
     * Processa e classifica a lista de dependências
     */
    protected function resolveDependencyList(array $dependencies, string $sourceType, string $sourceName): array
    {
        $unresolved = [];
        $catalog = null;

        // Processa plugins requeridos
        $requiredPlugins = $dependencies['plugins'] ?? [];
        foreach ($requiredPlugins as $pluginName => $config) {
            $folderName = Str::studly($pluginName);
            $folderPath = base_path("plugins/{$folderName}");
            $existsOnDisk = File::exists($folderPath);
            $dbRecord = Plugin::where('folder_name', $folderName)->first();
            $isActive = $dbRecord ? (bool) $dbRecord->is_active : false;

            if ($existsOnDisk && $isActive) {
                continue;
            }

            $reason = is_array($config) ? ($config['reason'] ?? null) : null;
            $customUrl = is_array($config) ? ($config['url'] ?? null) : null;

            // Se não está no disco, busca a URL no marketplace
            $downloadUrl = $customUrl;
            if (!$existsOnDisk && !$downloadUrl) {
                if ($catalog === null) {
                    $catalog = $this->marketplace->fetchCatalog();
                }
                foreach ($catalog['plugins'] ?? [] as $remoteItem) {
                    if (Str::studly($remoteItem['name']) === $folderName) {
                        $downloadUrl = $remoteItem['download_url'] ?? null;
                        break;
                    }
                }
            }

            $unresolved[] = [
                'type'         => 'plugin',
                'name'         => $pluginName,
                'folder'       => $folderName,
                'source_type'  => $sourceType,
                'source_name'  => $sourceName,
                'reason'       => $reason,
                'status'       => $existsOnDisk ? 'inactive' : 'missing',
                'db_id'        => $dbRecord ? $dbRecord->id : null,
                'download_url' => $downloadUrl,
            ];
        }

        return $unresolved;
    }
}
