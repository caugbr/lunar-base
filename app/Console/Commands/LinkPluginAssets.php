<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class LinkPluginAssets extends Command
{
    protected $signature = 'plugin:link
                            {plugin : Nome do plugin (ex: forms, calendar, billing)}
                            {--force : Remove o link existente antes de recriar}';

    protected $description = 'Cria link simbólico dos assets de um plugin em public/plugins/';

    public function handle(): int
    {
        $pluginArg    = $this->argument('plugin');
        $pluginStudly = Str::studly($pluginArg);
        $pluginLower  = Str::lower($pluginArg);

        $targetDir = base_path("plugins/{$pluginStudly}/resources/assets");
        $linkPath  = public_path("plugins/{$pluginLower}");

        if (! is_dir($targetDir)) {
            $this->error("✗ Diretório do plugin não encontrado:");
            $this->line("  Esperado: {$targetDir}");
            return self::FAILURE;
        }

        if (file_exists($linkPath) || is_link($linkPath)) {
            if (! $this->option('force')) {
                $this->warn("! Link já existe: {$linkPath}");
                $this->line("  Use --force para recriar.");
                return self::SUCCESS;
            }
            $this->removeExistingLink($linkPath);
        }

        $parentDir = dirname($linkPath);
        if (! is_dir($parentDir)) {
            mkdir($parentDir, 0755, true);
        }

        // Calcula o caminho relativo corretamente
        $relativeTarget = $this->getRelativePath($linkPath, $targetDir);

        $created = $this->createLink($relativeTarget, $linkPath, $targetDir);

        if (! $created) {
            $this->error("✗ Falha ao criar o link.");
            return self::FAILURE;
        }

        $this->info("✓ Link criado com sucesso:");
        $this->line("  {$linkPath}");
        $this->line("    → {$targetDir}");
        $this->line("    (relativo: {$relativeTarget})");
        $this->line("");
        $this->line("  Acesse em: " . url("plugins/{$pluginLower}/css/arquivo.css"));

        return self::SUCCESS;
    }

    /**
     * Calcula o caminho relativo entre dois caminhos absolutos.
     */
    private function getRelativePath(string $from, string $to): string
    {
        // Normaliza os caminhos
        $from = str_replace('\\', '/', realpath(dirname($from)));
        $to   = str_replace('\\', '/', realpath($to));

        $fromParts = explode('/', $from);
        $toParts   = explode('/', $to);

        // Encontra o prefixo comum
        $commonLength = 0;
        $max = min(count($fromParts), count($toParts));
        for ($i = 0; $i < $max; $i++) {
            if ($fromParts[$i] !== $toParts[$i]) break;
            $commonLength++;
        }

        // Calcula quantos níveis subir
        $upCount = count($fromParts) - $commonLength;
        $relativePath = str_repeat('../', $upCount);

        // Adiciona o caminho até o destino
        $relativePath .= implode('/', array_slice($toParts, $commonLength));

        return $relativePath;
    }

    /**
     * Cria o link simbólico detectando o sistema operacional.
     */
    private function createLink(string $target, string $link, string $absoluteTarget): bool
    {
        if (PHP_OS_FAMILY === 'Windows') {
            // No Windows, usa caminho ABSOLUTO para junctions (mais confiável)
            $cmd = sprintf(
                'mklink /J %s %s',
                escapeshellarg($link),
                escapeshellarg($absoluteTarget)
            );
            exec($cmd, $output, $returnCode);

            if ($returnCode !== 0) {
                $this->line("  Erro: " . implode("\n  ", $output));
            }

            return $returnCode === 0 && file_exists($link);
        }

        // Linux/macOS: usa caminho relativo
        return symlink($target, $link);
    }

    /**
     * Remove um link existente.
     */
    private function removeExistingLink(string $path): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            if (is_dir($path) && ! is_link($path)) {
                rmdir($path);
            } else {
                @unlink($path);
            }
        } else {
            if (is_link($path)) {
                unlink($path);
            }
        }
    }
}
