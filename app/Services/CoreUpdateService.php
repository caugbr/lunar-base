<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use ZipArchive;
use Exception;

class CoreUpdateService
{
    /**
     * Repositório oficial do GitHub para verificação e download de releases.
     */
    protected string $repo;

    /**
     * Arquivos em /config que pertencem estritamente ao Core da aplicação.
     * Estes arquivos DEVEM ser sobrescritos a cada release para que o sistema
     * receba novos esquemas, metadados e regras de segurança.
     *
     * Todos os outros arquivos de /config (ex: site.php, scripts.php, app.php)
     * serão preservados se já existirem na instalação local.
     */
    protected array $coreConfigFiles = [
        'settings.php',
        'rolesPermissions.php',
        'addons.php',
        'coreTranslatable.php',
        'tutorials.php',
        'defaultUsers.php',
    ];

    public function __construct()
    {
        $this->repo = env('GIT_SYSTEM_REPO', 'caugbr/lunar-base');
    }

    /**
     * Retorna informações sobre a atualização do Core, incluindo o Changelog da versão alvo.
     */
    // public function getUpdateInfo(): array
    // {
    //     $githubData = Cache::remember('lunar_github_latest_release', now()->addDay(), function () {
    //         return $this->fetchFromGitHub();
    //     });

    //     $currentVersion = appVersion();
    //     $latestTag      = $githubData['latest_version'] ?? $currentVersion;
    //     $hasUpdate      = version_compare($currentVersion, $latestTag, '<');

    //     // Se houver update, busca o changelog da nova versão; senão, busca da versão atual instalada
    //     $targetVersion = $hasUpdate ? $latestTag : $currentVersion;
    //     $changelogHtml = $this->getChangelogForVersion($targetVersion);

    //     return array_merge($githubData, [
    //         'current_version' => $currentVersion,
    //         'has_update'      => $hasUpdate,
    //         'target_version'  => $targetVersion,
    //         'changelog_html'  => $changelogHtml,
    //     ]);
    // }

    /**
     * Busca o CHANGELOG.md do GitHub e extrai apenas a seção da versão especificada.
     */
    // public functio

    /**
     * Retorna informações sobre a atualização do Core, incluindo o Changelog estruturado da família de versões.
     */
    public function getUpdateInfo(): array
    {
        $githubData = Cache::remember('lunar_github_latest_release', now()->addDay(), function () {
            return $this->fetchFromGitHub();
        });

        $currentVersion = appVersion();
        $latestTag      = $githubData['latest_version'] ?? $currentVersion;
        $hasUpdate      = version_compare($currentVersion, $latestTag, '<');

        // Se houver update, a versão alvo é a mais recente; senão, é a instalada
        $targetVersion = $hasUpdate ? $latestTag : $currentVersion;

        // Busca a lista de versões da mesma família (ex: 2.23.1, 2.23.0...)
        $changelogEntries = $this->getChangelogForVersion($targetVersion, $currentVersion);

        return array_merge($githubData, [
            'current_version'   => $currentVersion,
            'has_update'        => $hasUpdate,
            'target_version'    => $targetVersion,
            'changelog_entries' => $changelogEntries,
        ]);
    }

    /**
     * Busca o CHANGELOG.md do GitHub e extrai todas as versões da mesma família (MAJOR.MINOR.*).
     */
    public function getChangelogForVersion(string $targetVersion, string $currentVersion): array
    {
        $cacheKey = "lunar_changelog_family_{$targetVersion}_{$currentVersion}";

        return Cache::remember($cacheKey, now()->addDay(), function () use ($targetVersion, $currentVersion) {
            try {
                $url = "https://raw.githubusercontent.com/{$this->repo}/main/CHANGELOG.md";

                $response = Http::timeout(10)
                    ->withHeaders(['User-Agent' => 'Lunar-Base-Updater'])
                    ->get($url);

                if (!$response->successful()) {
                    $response = Http::timeout(10)
                        ->withHeaders(['User-Agent' => 'Lunar-Base-Updater'])
                        ->get("https://raw.githubusercontent.com/{$this->repo}/master/CHANGELOG.md");
                }

                if ($response->successful()) {
                    $entries = $this->extractFamilyEntriesFromChangelog($response->body(), $targetVersion, $currentVersion);
                    if (!empty($entries)) {
                        return $entries;
                    }
                }
            } catch (Exception $e) {
                logger()->warning("Não foi possível carregar o CHANGELOG.md remoto: " . $e->getMessage());
            }

            // Fallback: lê do arquivo local se existir
            $localChangelog = base_path('CHANGELOG.md');
            if (File::exists($localChangelog)) {
                $entries = $this->extractFamilyEntriesFromChangelog(File::get($localChangelog), $targetVersion, $currentVersion);
                if (!empty($entries)) {
                    return $entries;
                }
            }

            return [[
                'version'      => $targetVersion,
                'date'         => '',
                'is_new'       => version_compare($targetVersion, $currentVersion, '>'),
                'content_html' => "<p class=\"text-muted\">Nenhum registro detalhado encontrado para a versão v{$targetVersion}.</p>",
            ]];
        });
    }

    /**
     * Extrai todas as versões que pertencem à mesma série MAJOR.MINOR (ex: 2.23.*)
     * e define se cada uma é novidade ou se o usuário já possui instalada.
     */
    protected function extractFamilyEntriesFromChangelog(string $markdown, string $targetVersion, string $currentVersion): array
    {
        // Descobre a série (ex: de '2.23.1' extrai '2.23')
        if (!preg_match('/^(\d+\.\d+)/', $targetVersion, $m)) {
            return [];
        }
        $familyPrefix = $m[1];
        $escapedFamily = preg_quote($familyPrefix, '/');

        // Captura todos os blocos: ## [2.23.X] Data ... até o próximo ## [
        $pattern = '/##\s*\[(?<version>' . $escapedFamily . '\.[^\]]+)\][ \t]*(?<date>[^\r\n]*)\r?\n(?<body>.*?)(?=(?:\R##\s*\[|\z))/s';

        if (!preg_match_all($pattern, $markdown, $matches, PREG_SET_ORDER)) {
            return [];
        }

        $entries = [];

        foreach ($matches as $match) {
            $ver = trim($match['version']);
            $date = trim($match['date'], " -–\t\r\n");
            $body = trim($match['body']);

            // É novidade se for estritamente superior à versão instalada
            $isNew = version_compare($ver, $currentVersion, '>');

            $entries[] = [
                'version'      => $ver,
                'date'         => $date,
                'is_new'       => $isNew,
                'content_html' => Str::markdown($body, [
                    'html_input'         => 'escape',
                    'allow_unsafe_links' => false,
                ]),
            ];
        }

        return $entries;
    }

    /**
     * Extrai o bloco de texto entre "## [versao]" e a próxima ocorrência de "## ["
     */
    protected function extractVersionFromChangelog(string $markdown, string $version): string
    {
        $escapedVersion = preg_quote($version, '/');

        // Regex que captura desde '## [versao]' até o próximo '## [' ou fim do arquivo
        $pattern = "/##\s*\[" . $escapedVersion . "\].*?(?=(?:\R##\s*\[|\z))/s";

        if (preg_match($pattern, $markdown, $matches)) {
            // Remove a linha do título '## [versao] Data' para não duplicar na view
            $cleanContent = preg_replace('/^##\s*\[.*?\].*?\R+/m', '', trim($matches[0]));
            return trim($cleanContent);
        }

        return '';
    }

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
        $currentVersion = appVersion();

        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'User-Agent' => 'Lunar-Base-Updater',
                    'Accept'     => 'application/vnd.github.v3+json',
                ])
                ->withOptions([
                    'curl' => [
                        CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                    ],
                ])
                ->get("https://api.github.com/repos/{$this->repo}/releases/latest");

            if ($response->successful()) {
                $data = $response->json();
                $latestTag = ltrim($data['tag_name'] ?? $currentVersion, 'v');

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
            // Baixa o arquivo ZIP da release no GitHub
            $response = Http::timeout(120)
                ->withHeaders(['User-Agent' => 'Lunar-Base-Updater'])
                ->get($info['download_url']);

            if ($response->failed()) {
                throw new Exception("Falha ao baixar o arquivo de atualização do GitHub.");
            }

            File::put($tempZip, $response->body());

            // Extrai o arquivo ZIP
            $zip = new ZipArchive();
            if ($zip->open($tempZip) !== true) {
                throw new Exception("Não foi possível abrir o arquivo ZIP baixado.");
            }

            File::ensureDirectoryExists($tempExtract);
            $zip->extractTo($tempExtract);
            $zip->close();

            // Normaliza a subpasta interna criada automaticamente pelo GitHub
            $subDirs = File::directories($tempExtract);
            // $sourcePath = count($subDirs) === 1 ? $subDirs[0] : $tempExtract;
            $sourcePath = $tempExtract;
            foreach (File::directories($tempExtract) as $dir) {
                if (File::exists($dir . '/composer.json')) {
                    $sourcePath = $dir;
                    break;
                }
            }

            // Backup do banco SQLite se existir em database/database.sqlite
            $sqliteFile = base_path('database/database.sqlite');
            $sqliteBackup = storage_path('app/temp/database_sqlite_backup.sqlite');
            if (File::exists($sqliteFile)) {
                File::copy($sqliteFile, $sqliteBackup);
            }

            // Atualiza as pastas de código do Core esvaziando antes (incluindo 'lang')
            $coreFoldersToReplace = ['app', 'routes', 'resources', 'database', 'lang'];
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

            // Atualiza a pasta CONFIG de forma seletiva
            if (File::exists($sourcePath . '/config')) {
                $configFiles = File::files($sourcePath . '/config');

                foreach ($configFiles as $file) {
                    $fileName   = $file->getFilename();
                    $targetFile = base_path("config/{$fileName}");

                    // Arquivos essenciais do Core são sempre sobrescritos com a versão da release
                    if (in_array($fileName, $this->coreConfigFiles)) {
                        File::copy($file->getPathname(), $targetFile);
                        continue;
                    }

                    // Arquivos do projeto ou do Laravel só são copiados se não existirem localmente
                    if (!File::exists($targetFile)) {
                        File::copy($file->getPathname(), $targetFile);
                    }
                }
            }

            // Atualiza arquivos essenciais do Bootstrap (providers.php e app.php)
            // Preserva a pasta bootstrap/cache e suas permissões
            $bootstrapFiles = ['providers.php', 'app.php'];
            foreach ($bootstrapFiles as $bFile) {
                $sourceBFile = $sourcePath . '/bootstrap/' . $bFile;
                $targetBFile = base_path('bootstrap/' . $bFile);

                if (File::exists($sourceBFile)) {
                    File::ensureDirectoryExists(base_path('bootstrap'));
                    File::copy($sourceBFile, $targetBFile);
                }
            }

            // Sincroniza arquivos utilitários da raiz
            $rootFiles = ['VERSION', '.env.example', 'usecomposer.sh'];
            foreach ($rootFiles as $rFile) {
                $sourceRFile = $sourcePath . '/' . $rFile;
                if (File::exists($sourceRFile)) {
                    File::copy($sourceRFile, base_path($rFile));
                }
            }

            // Atualiza os assets da pasta public sem apagar uploads do usuário
            if (File::exists($sourcePath . '/public')) {
                File::copyDirectory($sourcePath . '/public', base_path('public'));
            }

            // Verifica e atualiza pacotes do Composer (Apenas se o lock mudou!)
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

                $isExecDisabled = !function_exists('exec')
                    || in_array('exec', array_map('trim', explode(',', (string) ini_get('disable_functions'))));

                if ($isExecDisabled) {
                    logger()->warning("Função exec() desabilitada no servidor. O composer install não pôde ser executado automaticamente.");

                    if (request()->hasSession()) {
                        session()->flash('warning', "Atenção: A função <code>exec()</code> está desabilitada neste servidor. O <code>composer install</code> da nova versão precisa ser executado manualmente.");
                    }
                } else {
                    $output = [];
                    $returnCode = 0;

                    if (PHP_OS_FAMILY === 'Windows') {
                        exec("composer install --no-dev 2>&1", $output, $returnCode);
                    } else {
                        $useComposerScript = base_path('usecomposer.sh');

                        if (File::exists($useComposerScript)) {
                            exec("bash \"" . $useComposerScript . "\" install --no-dev 2>&1", $output, $returnCode);
                        } else {
                            // Fallback caso o script usecomposer.sh não exista no Linux
                            exec("composer install --no-dev 2>&1", $output, $returnCode);
                        }
                    }

                    // Se a execução retornou código de erro diferente de 0
                    if ($returnCode !== 0) {
                        $errorOutput = implode("\n", $output);
                        logger()->error("Falha ao executar composer install. Código: {$returnCode}. Saída:\n{$errorOutput}");

                        if (request()->hasSession()) {
                            session()->flash('warning', "Atenção: A atualização de dependências via Composer retornou um erro. Verifique os logs do sistema.");
                        }
                    }
                }
            }

            // Roda migrações do banco e limpa os caches do Laravel
            Artisan::call('migrate', ['--force' => true]);
            Artisan::call('optimize:clear');

            // Garante que o link simbólico do storage continue ativo
            if (!File::exists(public_path('storage'))) {
                Artisan::call('storage:link');
            }

            // Limpa a chave correta de cache da release para refletir na hora a nova versão
            Cache::forget('lunar_github_latest_release');

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
