<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ThemeCreate extends Command
{
    protected $signature = 'theme:create {name : O nome do tema} {description? : Uma descrição opcional}';
    protected $description = 'Gera a estrutura base de um novo tema';

    public function handle(): int
    {
        $inputName = $this->argument('name');

        // Gera o slug limpo (ex: "Dark Mode" -> "dark-mode" | "FAQ Theme" -> "faq-theme")
        $slugName = Str::slug($inputName);

        // Gera o StudlyCase limpo para a pasta (ex: "dark-mode" -> "DarkMode" | "faq-theme" -> "FaqTheme")
        $studlyName = Str::studly($slugName);

        $themePath = base_path("themes/{$studlyName}");

        if (File::exists($themePath)) {
            $this->error("Tema '{$studlyName}' já existe!");
            return Command::FAILURE;
        }

        $description = $this->argument('description') ?? "Um tema customizado para Lunar Base.";
        $this->info("Iniciando setup para o tema '{$studlyName}'...");

        // 1. Carrega as tags/categorias de temas do config('addons.tags.theme')
        $themeTagsConfig = config('addons.tags.theme', []);
        $choices = [];
        $slugMap = [];

        $cnt = 1;
        foreach ($themeTagsConfig as $slug => $category) {
            $label   = $category['name'] ?? ucfirst($slug);
            $desc    = !empty($category['description']) ? " - {$category['description']}" : "";
            $display = $label . $desc;

            $choices[$cnt]     = $display;
            $slugMap[$display] = $slug;
            $cnt++;
        }

        // 2. Seleção clássica por números (iniciando em 1, compatível com qualquer terminal)
        $selectedLabels = [];
        if (!empty($choices)) {
            $selectedLabels = $this->choice(
                'Selecione as categorias/tags do tema (digite os números separados por vírgula, ex: 1,3):',
                $choices,
                null,
                null,
                true // Permite seleção múltipla
            );
        }

        // 3. Converte os textos selecionados de volta para os slugs ('blog', 'corporate', etc.)
        $selectedTags = [];
        if (is_array($selectedLabels)) {
            foreach ($selectedLabels as $selectedText) {
                if (isset($slugMap[$selectedText])) {
                    $selectedTags[] = $slugMap[$selectedText];
                }
            }
        }

        // 4. Criação de diretórios
        $directories = [
            $themePath,
            $themePath . '/resources/assets',
            $themePath . '/resources/assets/images',
            $themePath . '/resources/assets/css',
            $themePath . '/resources/assets/css/public',
            $themePath . '/resources/assets/js',
            $themePath . '/resources/views'
        ];

        foreach ($directories as $dir) {
            File::ensureDirectoryExists($dir, 0755, true);
        }

        // 5. Criar arquivo theme.json com as tags
        $manifest = [
            'name'        => Str::headline($inputName),
            'description' => $description,
            'version'     => '1.0.0',
            'author'      => 'Lunar Developer',
            'tags'        => array_values(array_unique($selectedTags)),
            'screenshot'  => 'resources/assets/images/screenshot.png'
        ];

        File::put(
            $themePath . '/theme.json',
            json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        );

        // 6. Clonar a estrutura de views originais
        $sourceViews = resource_path('views/public');
        $destinationViews = $themePath . '/resources/views/public';

        if (File::exists($sourceViews)) {
            File::copyDirectory($sourceViews, $destinationViews);
            $this->info("Estrutura de views clonada com sucesso.");
        } else {
            $this->warn("Aviso: O diretório original 'resources/views/public' não foi encontrado.");
        }

        // 7. Copiar os CSSs públicos originais para o tema
        $sourceCss = public_path('css/public');
        $destinationBaseCss = $themePath . '/resources/assets/css';
        $destinationCss = $destinationBaseCss . '/public';

        $cssFiles = [
            public_path('css') . "/auth.css",
            public_path('css') . "/dialog.css",
            public_path('css') . "/errors.css"
        ];

        if (File::exists($sourceCss)) {
            File::copyDirectory($sourceCss, $destinationCss);
            $this->info("CSSs públicos clonados para o tema.");
        } else {
            $this->warn("Aviso: O diretório 'public/css/public' não foi encontrado.");
        }

        foreach ($cssFiles as $file) {
            if (File::exists($file)) {
                $fileName = basename($file);
                File::copy($file, $destinationBaseCss . '/' . $fileName);
                $this->info("Arquivo CSS adicional '{$fileName}' clonado com sucesso em 'assets/css'.");
            } else {
                $this->warn("Aviso: O arquivo CSS '{$file}' não foi encontrado para cópia.");
            }
        }

        // 8. Reescrever as referências de asset nas views do tema
        if (File::exists($destinationViews)) {
            $this->rewriteAssetPaths($destinationViews, $slugName);
            $this->info("Referências de asset atualizadas nas views do tema.");
        }

        $this->info("--------------------------------------------------");
        $this->info("Tema '{$studlyName}' criado com sucesso!");
        $this->warn("Path: themes/{$studlyName}");
        $this->info("Tags associadas: " . (empty($selectedTags) ? 'Nenhuma' : implode(', ', $selectedTags)));
        $this->info("--------------------------------------------------");

        return Command::SUCCESS;
    }

    /**
     * Percorre recursivamente todas as views do tema e reescreve
     * asset('css/...) para asset('themes/{slugName}/css/...)
     */
    protected function rewriteAssetPaths(string $viewsDir, string $slugName): void
    {
        $files = File::allFiles($viewsDir);

        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $path = $file->getPathname();
            $content = File::get($path);

            if (str_contains($path, "moon-loader.css")) {
                continue;
            }

            // Substitui referencias usando o $slugName limpo
            $newContent = preg_replace(
                '/asset\((["\'])css\//',
                'asset($1themes/' . $slugName . '/css/',
                $content
            );

            if ($newContent !== $content) {
                File::put($path, $newContent);
            }
        }
    }
}
