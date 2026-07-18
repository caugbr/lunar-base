<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class UpdateTutorials extends Command
{
    protected $signature = 'tutorials:update {--force : Forçar a atualização mesmo sem alterações detectadas}';
    protected $description = 'Atualiza dinamicamente valores em arquivos HTML estáticos usando Regex';

    public function handle()
    {
        $this->info('Iniciando atualização dos tutoriais estáticos...');

        $directory = config('tutorials.directory');
        $replacements = config('tutorials.replacements', []);

        if (!File::isDirectory($directory)) {
            $this->error("⚠️ O diretório '{$directory}' não existe.");
            return Command::FAILURE;
        }

        // Pega todos os arquivos .html no diretório (e subdiretórios)
        $files = File::allFiles($directory);
        $htmlFiles = array_filter($files, fn($file) => $file->getExtension() === 'html');

        if (empty($htmlFiles)) {
            $this->warn('Nenhum arquivo .html encontrado no diretório configurado.');
            return Command::SUCCESS;
        }

        $bar = $this->output->createProgressBar(count($htmlFiles));
        $bar->start();

        $updatedCount = 0;

        foreach ($htmlFiles as $file) {
            $content = File::get($file->getPathname());
            $originalContent = $content;

            // Aplica todas as regras de Regex sequencialmente
            foreach ($replacements as $rule) {
                $content = preg_replace($rule['pattern'], $rule['replace'], $content);
            }

            // Salva apenas se o conteúdo mudou (ou se --force foi passado)
            if ($content !== $originalContent || $this->option('force')) {
                File::put($file->getPathname(), $content);
                $this->info("\nAtualizado: " . $file->getFilename());
                $updatedCount++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        if ($updatedCount > 0) {
            $this->info("Processo concluído! {$updatedCount} arquivo(s) modificado(s).");
        } else {
            $this->info("Processo concluído! Nenhum arquivo precisava de atualização.");
        }

        return Command::SUCCESS;
    }
}
