<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ThemeCreateCommand extends Command
{
    protected $signature = 'theme:create {name : O nome do tema} {description? : Uma descrição opcional}';
    protected $description = 'Gera a estrutura base de um novo tema';

    public function handle(): int
    {
        $inputName = $this->argument('name');
        $studlyName = Str::studly($inputName);
        $kebabName = Str::kebab($studlyName);
        $themePath = base_path("themes/{$studlyName}");

        if (File::exists($themePath)) {
            $this->error("Tema '{$studlyName}' já existe!");
            return Command::FAILURE;
        }

        $description = $this->argument('description') ?? "Um tema customizado para Lunar Base.";
        $this->info("Gerando tema '{$studlyName}'...");

        // 1. Criação de diretórios
        $directories = [
            $themePath,
            $themePath . '/resources/assets',
            $themePath . '/resources/assets/images',
            $themePath . '/resources/assets/css',
            $themePath . '/resources/assets/css/public', // <-- NOVO: destino dos CSSs
            $themePath . '/resources/assets/js',
            $themePath . '/resources/views'
        ];

        foreach ($directories as $dir) {
            File::ensureDirectoryExists($dir, 0755, true);
        }

        // 2. Criar arquivo theme.json
        $manifest = [
            'name' => Str::headline($studlyName),
            'description' => $description,
            'version' => '1.0.0',
            'author' => 'Lunar Developer',
            'screenshot' => 'resources/assets/images/screenshot.png'
        ];
        File::put($themePath . '/theme.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        // 3. Clonar a estrutura de views originais
        $sourceViews = resource_path('views/public');
        $destinationViews = $themePath . '/resources/views/public';

        if (File::exists($sourceViews)) {
            File::copyDirectory($sourceViews, $destinationViews);
            $this->info("Estrutura de views clonada com sucesso.");
        } else {
            $this->warn("Aviso: O diretório original 'resources/views/public' não foi encontrado.");
        }

        // 4. Copiar os CSSs públicos originais para o tema (NOVO)
        $sourceCss = public_path('css/public');
        $destinationBaseCss = $themePath . '/resources/assets/css';
        $destinationCss = $destinationBaseCss . '/public';

        $cssFiles = [
            public_path('css') . "/auth.css",
            public_path('css') . "/dialog.css",
            public_path('css') . "/errors.css"
        ];

        // Copiar o diretório de CSS público (css/public -> assets/css/public)
        if (File::exists($sourceCss)) {
            File::copyDirectory($sourceCss, $destinationCss);
            $this->info("CSSs públicos clonados para o tema.");
        } else {
            $this->warn("Aviso: O diretório 'public/css/public' não foi encontrado.");
        }

        // Copiar os arquivos adicionais avulsos para a pasta pai (css/arquivo.css -> assets/css/arquivo.css)
        foreach ($cssFiles as $file) {
            if (File::exists($file)) {
                $fileName = basename($file);

                // Destino ajustado para a pasta base ($destinationBaseCss), fora da pasta /public
                File::copy($file, $destinationBaseCss . '/' . $fileName);
                $this->info("Arquivo CSS adicional '{$fileName}' clonado com sucesso em 'assets/css'.");
            } else {
                $this->warn("Aviso: O arquivo CSS '{$file}' não foi encontrado para cópia.");
            }
        }

        // 5. Reescrever as referências de asset nas views do tema (NOVO)
        // Converte: asset('css/...)  →  asset('themes/theme-name/css/...)
        if (File::exists($destinationViews)) {
            $this->rewriteAssetPaths($destinationViews, $kebabName);
            $this->info("Referências de asset atualizadas nas views do tema.");
        }

        $this->info("--------------------------------------------------");
        $this->info("Tema '{$studlyName}' criado com sucesso!");
        $this->warn("Path: themes/{$studlyName}");
        $this->info("--------------------------------------------------");

        // $this->createPublicAssetLink($kebabName, $themePath);

        return Command::SUCCESS;
    }

    /**
     * Percorre recursivamente todas as views do tema e reescreve
     * asset('css/...) para asset('themes/{theme}/css/...)
     */
    protected function rewriteAssetPaths(string $viewsDir, string $kebabName): void
    {
        $files = File::allFiles($viewsDir);

        foreach ($files as $file) {
            // Só processa arquivos Blade
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $path = $file->getPathname();
            $content = File::get($path);

            if (str_contains($path, "moon-loader.css")) {
                continue;
            }

            // Regex: asset('css/  ou  asset("css/
            // Preserva a aspa usada ($1) e redireciona para o tema
            $newContent = preg_replace(
                '/asset\((["\'])css\//',
                'asset($1themes/' . $kebabName . '/css/',
                $content
            );

            if ($newContent !== $content) {
                File::put($path, $newContent);
            }
        }
    }

    protected function createPublicAssetLink(string $kebabName, string $themePath): void
    {
        // Chama o comando de linkagem de tema que já calcula o caminho relativo
        $this->call('theme:link', [
            'theme' => $kebabName,
            '--force' => true
        ]);
    }
}
