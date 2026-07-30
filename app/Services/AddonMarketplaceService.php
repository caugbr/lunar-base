<?php

namespace App\Services;

use App\Models\Plugin;
use App\Models\Theme;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class AddonMarketplaceService
{
    protected string $repo;
    protected string $manifestUrl;

    public function __construct()
    {
        $this->repo = env('GIT_ADDONS_REPO', 'caugbr/lunar-base-addons');
        $this->manifestUrl = "https://github.com/{$this->repo}/releases/latest/download/marketplace.json";
    }

    /**
     * Retorna todos os plugins disponíveis.
     */
    public function getAvailablePlugins(): array
    {
        $catalog = $this->fetchCatalog();

        $installedPlugins = Plugin::all()->keyBy('folder_name');

        return array_map(function ($addon) use ($installedPlugins) {
            return $this->formatAddonData($addon, 'plugin', $installedPlugins);
        }, $catalog['plugins'] ?? []);
    }

    /**
     * Retorna todos os temas disponíveis.
     */
    public function getAvailableThemes(): array
    {
        $catalog = $this->fetchCatalog();

        // Gera a chave em kebab-case dinamicamente para cada tema
        $installedThemes = Theme::all()->keyBy(fn ($theme) => Str::kebab($theme->folder_name ?? $theme->name));

        return array_map(function ($addon) use ($installedThemes) {
            return $this->formatAddonData($addon, 'theme', $installedThemes);
        }, $catalog['themes'] ?? []);
    }

    /**
     * Formata e adiciona metadados de status para um Addon (plugin ou tema).
     */
    protected function formatAddonData(array $addon, string $type, $installedCollection): array
    {
        $name   = $addon['name'];
        $folder = Str::studly($name);  // ex: QrCode

        $baseDir      = $type === 'theme' ? 'themes' : 'plugins';
        $folderExists = File::exists(base_path("{$baseDir}/{$folder}"));
        $dbRecord     = $installedCollection->get($folder);

        return array_merge($addon, [
            'type'         => $type, // 'plugin' ou 'theme'
            'folder'       => $folder,
            'is_installed' => $folderExists || $dbRecord !== null,
            'is_active'    => $dbRecord ? (bool) $dbRecord->is_active : false,
            'db_id'        => $dbRecord ? $dbRecord->id : null,
        ]);
    }

    /**
     * Busca o catálogo remoto com cache de 1 hora.
     */
    public function fetchCatalog(): array
    {
        return Cache::remember('lunar_marketplace_catalog', now()->addHour(), function () {
            try {
                $response = Http::timeout(5)->get($this->manifestUrl);
                if ($response->successful()) {
                    return $response->json();
                }
            } catch (\Exception $e) {
                logger()->error("Erro ao carregar o catálogo de addons: " . $e->getMessage());
            }

            return ['plugins' => [], 'themes' => []];
        });
    }

    public function clearCache(): void
    {
        Cache::forget('lunar_marketplace_catalog');
    }
}
