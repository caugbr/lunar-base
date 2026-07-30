<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use ZipArchive;
use Exception;

class CoreUpdateService
{
    protected string $repo;

    public function __construct()
    {
        $this->repo = env('GIT_SYSTEM_REPO', 'caugbr/lunar-base');
    }

    /**
     * Retorna informações sobre a atualização do Core
     */
    public function getUpdateInfo(): array
    {
        $githubData = Cache::remember('lunar_github_latest_release', now()->addDay(), function () {
            return $this->fetchFromGitHub();
        });

        $currentVersion = config('app.version', '1.2.0');
        $latestTag      = $githubData['latest_version'] ?? $currentVersion;

        $hasUpdate = version_compare($currentVersion, $latestTag, '<');

        return array_merge($githubData, [
            'current_version' => $currentVersion,
            'has_update'      => $hasUpdate,
        ]);
    }

    /**
     * Força a rechecagem limpando o cache do GitHub
     */
    public function checkForUpdates(): array
    {
        Cache::forget('lunar_github_latest_release');
        return $this->getUpdateInfo();
    }

    /**
     * Consulta a API de Releases do GitHub
     */
    protected function fetchFromGitHub(): array
    {
        $currentVersion = config('app.version', '1.2.0');

        try {
            $response = Http::timeout(5)
                ->withHeaders(['User-Agent' => 'Lunar-Base-Updater'])
                ->get("https://api.github.com/repos/{$this->repo}/releases/latest");

            if ($response->successful()) {
                $data = $response->json();
                $latestTag = ltrim($data['tag_name'] ?? '1.2.0', 'v');

                $hasUpdate = version_compare($currentVersion, $latestTag, '<');

                return [
                    'success'         => true,
                    'has_update'      => $hasUpdate,
                    'current_version' => $currentVersion,
                    'latest_version'  => $latestTag,
                    'changelog'       => $data['body'] ?? '',
                    'download_url'    => $data['zipball_url'] ?? null,
                ];
            }
        } catch (Exception $e) {
            logger()->error("Erro ao verificar atualização do Core: " . $e->getMessage());
        }

        return [
            'success'         => false,
            'has_update'      => false,
            'current_version' => $currentVersion,
            'latest_version'  => $currentVersion,
            'changelog'       => '',
            'download_url'    => null,
        ];
    }

    /**
     * Baixa a atualização, sobrescreve o Core e executa migrações
     */
    public function applyUpdate(): bool
    {
        $info = $this->fetchFromGitHub();

        if (!$info['has_update'] || empty($info['download_url'])) {
            throw new Exception("Nenhuma atualização disponível.");
        }

        $tempZip     = storage_path('app/temp/core_update.zip');
        $tempExtract = storage_path('app/temp/core_extracted');

        File::ensureDirectoryExists(storage_path('app/temp'));

        try {
            // 1. Baixa o ZIP da release
            $response = Http::timeout(120)
                ->withHeaders(['User-Agent' => 'Lunar-Base-Updater'])
                ->get($info['download_url']);

            if ($response->failed()) {
                throw new Exception("Falha ao baixar o arquivo de atualização do GitHub.");
            }

            File::put($tempZip, $response->body());

            // 2. Extrai o arquivo
            $zip = new ZipArchive();
            if ($zip->open($tempZip) !== true) {
                throw new Exception("Não foi possível abrir o arquivo ZIP baixado.");
            }

            File::ensureDirectoryExists($tempExtract);
            $zip->extractTo($tempExtract);
            $zip->close();

            // 3. Normaliza a pasta interna criada pelo GitHub
            $subDirs = File::directories($tempExtract);
            $sourcePath = count($subDirs) === 1 ? $subDirs[0] : $tempExtract;

            // 4. Sobrescreve apenas as pastas do Core (PRESERVA .env, storage, plugins, themes)
            $coreFolders = ['app', 'config', 'routes', 'resources', 'public'];
            foreach ($coreFolders as $folder) {
                $src  = $sourcePath . '/' . $folder;
                $dest = base_path($folder);

                if (File::exists($src)) {
                    File::copyDirectory($src, $dest);
                }
            }

            // 5. Roda migrações e limpa os caches
            Artisan::call('migrate', ['--force' => true]);
            Artisan::call('optimize:clear');

            Cache::forget('lunar_core_update_check');

            return true;
        } catch (Exception $e) {
            logger()->error("Erro ao aplicar atualização do Core: " . $e->getMessage());
            throw $e;
        } finally {
            File::deleteDirectory($tempExtract);
            File::delete($tempZip);
        }
    }
}
