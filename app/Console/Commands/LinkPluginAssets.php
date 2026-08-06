<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class LinkPluginAssets extends Command
{
    protected $signature = 'plugin:link
                            {plugin : Nome do plugin (ex: forms, calendar, billing)}
                            {--force : Remove o link existente antes de recriar}
                            {--unlink : Remove o link simbólico do plugin em vez de criar}';

    protected $description = 'Cria link simbólico dos assets de um plugin em public/plugins/';

    public function handle(): int
    {
        $pluginArg = $this->argument('plugin');

        // 1. Gera o SLUG limpo para o atalho público (ex: "Prism Highlight" -> "prism-highlight" | "FAQ" -> "faq")
        $pluginSlug = Str::slug(preg_replace('/([a-z0-9])([A-Z])/', '$1-$2', $pluginArg));

        // 2. Busca a pasta física em /plugins/
        // Opção A: Nome em StudlyCase a partir do slug (ex: "PrismHighlight" ou "Faq")
        $pluginStudly = Str::studly($pluginSlug);
        $targetDir    = base_path("plugins/{$pluginStudly}/resources/assets");

        // Opção B: Fallback para o nome exato digitado (ex: "FAQ" ou "PrismHighlight")
        if (! is_dir($targetDir)) {
            $exactDir = base_path("plugins/{$pluginArg}/resources/assets");
            if (is_dir($exactDir)) {
                $targetDir = $exactDir;
            }
        }

        $linkPath = public_path("plugins/{$pluginSlug}");

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

        $created = $this->createLink($linkPath, $targetDir);

        if (! $created) {
            $this->error("✗ Falha ao criar o link.");
            return self::FAILURE;
        }

        $this->info("✓ Link criado com sucesso:");
        $this->line("  {$linkPath}");
        $this->line("    → {$targetDir}");
        $this->line("");
        $this->line("  Acesse em: " . url("plugins/{$pluginSlug}/css/arquivo.css"));

        return self::SUCCESS;
    }

    /**
     * Cria o link usando a mesma Engine nativa do 'php artisan storage:link'
     */
    private function createLink(string $link, string $absoluteTarget): bool
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
     * Remove um link ou pasta existente usando a Facade do Laravel
     */
    private function removeExistingLink(string $path): void
    {
        if (is_link($path)) {
            File::delete($path);
        } elseif (is_dir($path)) {
            File::deleteDirectory($path);
        } else {
            File::delete($path);
        }
    }
}
