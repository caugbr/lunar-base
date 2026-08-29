<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class LinkThemeAssets extends Command
{
    protected $signature = 'theme:link
                            {theme : Nome do tema (ex: "FJS Theme", "Lunar Apps" ou "lunar-apps")}
                            {--force : Remove o link existente antes de recriar}
                            {--unlink : Remove o link simbólico em vez de criar}';

    protected $description = 'Cria link simbólico dos assets de um tema em public/themes/';

    public function handle(): int
    {
        $themeArg = $this->argument('theme');

        // 1. Gera o SLUG limpo para o atalho público (ex: "FJS Theme" -> "fjs-theme" | "Lunar Apps" -> "lunar-apps")
        $themeSlug = Str::slug(preg_replace('/([a-z0-9])([A-Z])/', '$1-$2', $themeArg));

        // 2. Busca a pasta física em /themes/
        // Opção A: Nome em StudlyCase a partir do slug (ex: "FjsTheme" ou "LunarApps")
        $themeStudly = Str::studly($themeSlug);
        $targetDir   = base_path("themes/{$themeStudly}/resources/assets");

        // Opção B: Fallback para o nome exato digitado (ex: "FJSTheme")
        if (! is_dir($targetDir)) {
            $exactDir = base_path("themes/{$themeArg}/resources/assets");
            if (is_dir($exactDir)) {
                $targetDir = $exactDir;
            }
        }

        $linkPath = public_path("themes/{$themeSlug}");

        if ($this->option('unlink')) {
            if (file_exists($linkPath) || is_link($linkPath)) {
                $this->removeExistingLink($linkPath);
                $this->info("✓ Link/Pasta removido com sucesso: {$linkPath}");
            } else {
                $this->line("! Nenhum link encontrado para remover: {$linkPath}");
            }
            return self::SUCCESS;
        }

        if (! is_dir($targetDir)) {
            $this->error("✗ Diretório do theme não encontrado:");
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
            $this->error("✗ Falha ao criar o link do tema.");
            return self::FAILURE;
        }

        $this->info("✓ Link criado com sucesso:");
        $this->line("  {$linkPath}");
        $this->line("    → {$targetDir}");
        $this->line("");
        $this->line("  Acesse em: " . url("themes/{$themeSlug}/css/arquivo.css"));

        return self::SUCCESS;
    }

    /**
     * Calcula o caminho relativo entre dois caminhos absolutos.
     */
    private function getRelativePath(string $from, string $to): string
    {
        $fromDir = realpath(dirname($from));
        $toDir   = realpath($to);

        if (! $fromDir || ! $toDir) {
            return $to;
        }

        $fromDir = str_replace('\\', '/', $fromDir);
        $toDir   = str_replace('\\', '/', $toDir);

        $fromParts = explode('/', $fromDir);
        $toParts   = explode('/', $toDir);

        $commonLength = 0;
        $max = min(count($fromParts), count($toParts));
        for ($i = 0; $i < $max; $i++) {
            if ($fromParts[$i] !== $toParts[$i]) break;
            $commonLength++;
        }

        $upCount = count($fromParts) - $commonLength;
        $relativePath = str_repeat('../', $upCount);
        $relativePath .= implode('/', array_slice($toParts, $commonLength));

        return $relativePath;
    }

    /**
     * Cria o link usando a mesma Engine nativa do 'php artisan storage:link'
     */
    private function createLink(string $target, string $link, string $absoluteTarget): bool
    {
        try {
            // Usa a Facade oficial do Laravel (igualzinho ao storage:link)
            File::link($absoluteTarget, $link);

            return file_exists($link) || is_link($link);
        } catch (\Throwable $e) {
            // Se o servidor proibir links por política de segurança, copia a pasta
            $this->warn("  ! Não foi possível criar link simbólico. Copiando arquivos...");
            File::copyDirectory($absoluteTarget, $link);

            return is_dir($link);
        }
    }

    /**
     * Remove um link simbólico ou pasta existente com compatibilidade Windows/Linux
     */
    private function removeExistingLink(string $path): void
    {
        // Normaliza barras para o padrão do SO
        $normalizedPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);

        // 1. Se for Windows
        if (PHP_OS_FAMILY === 'Windows') {
            // No Windows, links de diretório são removidos com rmdir do PHP ou rmdir do CMD
            if (is_dir($normalizedPath)) {
                // Tenta remover o atalho/link sem apagar o conteúdo da pasta original
                if (! @rmdir($normalizedPath)) {
                    // Fallback via comando nativo do Windows para Junctions/Symlinks
                    @exec(sprintf('rd /s /q "%s"', $normalizedPath));
                }
            } elseif (file_exists($normalizedPath)) {
                @unlink($normalizedPath);
            }
            return;
        }

        // 2. Se for Linux / MacOS
        if (is_link($normalizedPath)) {
            File::delete($normalizedPath);
        } elseif (is_dir($normalizedPath)) {
            File::deleteDirectory($normalizedPath);
        } else {
            File::delete($normalizedPath);
        }
    }
}
