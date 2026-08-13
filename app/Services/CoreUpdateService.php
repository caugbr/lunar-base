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
     * Aplica a atualização do Core baixando e substituindo os arquivos de forma segura.
     *
     * @return bool
     * @throws Exception
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
            // 1. Baixa o arquivo ZIP da release no GitHub
            $response = Http::timeout(120)
                ->withHeaders(['User-Agent' => 'Lunar-Base-Updater'])
                ->get($info['download_url']);

            if ($response->failed()) {
                throw new Exception("Falha ao baixar o arquivo de atualização do GitHub.");
            }

            File::put($tempZip, $response->body());

            // 2. Extrai o arquivo ZIP
            $zip = new ZipArchive();
            if ($zip->open($tempZip) !== true) {
                throw new Exception("Não foi possível abrir o arquivo ZIP baixado.");
            }

            File::ensureDirectoryExists($tempExtract);
            $zip->extractTo($tempExtract);
            $zip->close();

            // 3. Normaliza a subpasta interna criada automaticamente pelo GitHub
            $subDirs = File::directories($tempExtract);
            $sourcePath = count($subDirs) === 1 ? $subDirs[0] : $tempExtract;

            // 4. Backup do banco SQLite se existir em database/database.sqlite
            $sqliteFile = base_path('database/database.sqlite');
            $sqliteBackup = storage_path('app/temp/database_sqlite_backup.sqlite');
            if (File::exists($sqliteFile)) {
                File::copy($sqliteFile, $sqliteBackup);
            }

            // 5. Atualiza as pastas de código do Core esvaziando antes (ELIMINA ARQUIVOS FANTASMA)
            $coreFoldersToReplace = ['app', 'routes', 'resources', 'database'];
            foreach ($coreFoldersToReplace as $folder) {
                $src  = $sourcePath . '/' . $folder;
                $dest = base_path($folder);

                if (File::exists($src)) {
                    File::deleteDirectory($dest);
                    File::copyDirectory($src, $dest);
                }
            }

            // Restaura o banco SQLite se ele existia
            if (File::exists($sqliteBackup)) {
                File::copy($sqliteBackup, $sqliteFile);
                File::delete($sqliteBackup);
            }

            // 6. Atualiza a pasta CONFIG de forma ADITIVA (Só copia arquivos novos!)
            if (File::exists($sourcePath . '/config')) {
                $configFiles = File::files($sourcePath . '/config');

                foreach ($configFiles as $file) {
                    $fileName   = $file->getFilename();
                    $targetFile = base_path("config/{$fileName}");

                    if (!File::exists($targetFile)) {
                        File::copy($file->getPathname(), $targetFile);
                    }
                }
            }

            // 7. Atualiza o arquivo de versão na raiz do projeto (VERSION)
            if (File::exists($sourcePath . '/VERSION')) {
                File::copy($sourcePath . '/VERSION', base_path('VERSION'));
            }

            // 8. Atualiza os assets da pasta public sem apagar uploads do usuário
            if (File::exists($sourcePath . '/public')) {
                File::copyDirectory($sourcePath . '/public', base_path('public'));
            }

            // 9. VERIFICA E ATUALIZA PACOTES DO COMPOSER (Apenas se o composer.lock mudou!)
            $oldLock = base_path('composer.lock');
            $newLock = $sourcePath . '/composer.lock';
            $needsComposerUpdate = false;

            if (File::exists($newLock)) {
                if (!File::exists($oldLock) || File::hash($oldLock) !== File::hash($newLock)) {
                    $needsComposerUpdate = true;
                }
                File::copy($newLock, $oldLock);
            }

            if (File::exists($sourcePath . '/composer.json')) {
                File::copy($sourcePath . '/composer.json', base_path('composer.json'));
            }

            if ($needsComposerUpdate) {
                logger()->info("Alteração no composer.lock detectada. Rodando atualização de pacotes...");

                if (PHP_OS_FAMILY === 'Windows') {
                    exec("composer install 2>&1", $output, $returnCode);
                } else {
                    $useComposerScript = base_path('usecomposer.sh');
                    if (File::exists($useComposerScript)) {
                        exec("bash \"" . $useComposerScript . "\" install --no-dev 2>&1", $output, $returnCode);
                    }
                }
            }

            // 10. Roda migrações do banco e limpa os caches do Laravel
            Artisan::call('migrate', ['--force' => true]);
            Artisan::call('optimize:clear');

            // Garante que o link simbólico do storage continue ativo
            if (!File::exists(public_path('storage'))) {
                Artisan::call('storage:link');
            }

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
