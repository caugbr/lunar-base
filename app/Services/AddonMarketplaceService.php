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

        // Chaveia pela pasta física/Studly (ex: BlackTheme)
        $installedThemes = Theme::all()->keyBy(fn ($theme) => Str::studly($theme->folder_name ?? $theme->name));

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
        $folder = Str::studly($name);  // ex: QrCode / BlackTheme

        $baseDir      = $type === 'theme' ? 'themes' : 'plugins';
        $folderExists = File::exists(base_path("{$baseDir}/{$folder}"));
        $dbRecord     = $installedCollection->get($folder);

        // Versão instalada localmente no seu banco
        $localVersion = $dbRecord ? $dbRecord->version : null;

        // Versão remota vinda do marketplace.json do GitHub
        $remoteVersion = $addon['version'] ?? '1.0.0';

        // Verifica se a versão remota é maior que a local
        $hasUpdate = $dbRecord && version_compare($remoteVersion, $localVersion, '>');

        return array_merge($addon, [
            'type'           => $type,
            'folder'         => $folder,
            'is_installed'   => $folderExists || $dbRecord !== null,
            'is_active'      => $dbRecord ? (bool) $dbRecord->is_active : false,
            'db_id'          => $dbRecord ? $dbRecord->id : null,
            'local_version'  => $localVersion,
            'remote_version' => $remoteVersion,
            'has_update'     => $hasUpdate,
        ]);
    }

    /**
     * Busca o catálogo remoto com cache de 1 hora.
     */
    // public function fetchCatalog(): array
    // {
    //     return Cache::remember('lunar_marketplace_catalog', now()->addHour(), function () {
    //         try {
    //             $response = Http::timeout(5)->get($this->manifestUrl);
    //             if ($response->successful()) {
    //                 return $response->json();
    //             }
    //         } catch (\Exception $e) {
    //             logger()->error("Erro ao carregar o catálogo de addons: " . $e->getMessage());
    //         }

    //         return ['plugins' => [], 'themes' => []];
    //     });
    // }
    public function fetchCatalog(): array
    {
        return Cache::remember('lunar_marketplace_catalog', now()->addHour(), function () {
            try {
                $response = Http::timeout(20)
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                        'Accept'     => '*/*',
                    ])
                    ->withOptions([
                        'curl' => [
                            CURLOPT_IPRESOLVE       => CURL_IPRESOLVE_V4,
                            CURLOPT_FOLLOWLOCATION  => true, // Obriga o cURL a seguir os saltos
                            CURLOPT_MAXREDIRS       => 5,    // Permite até 5 redirects (temos 2 aqui)
                            CURLOPT_AUTOREFERER     => true,
                        ],
                        'allow_redirects' => [
                            'max'             => 5,
                            'strict'          => false,
                            'referer'         => true,
                            'protocols'       => ['http', 'https'],
                            'track_redirects' => true
                        ],
                    ])
                    ->get($this->manifestUrl);

                if ($response->successful()) {
                    return $response->json();
                }

                logger()->warning("Falha ao obter catálogo. Status HTTP: " . $response->status());
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
