<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ThemeEditTags extends Command
{
    protected $signature = 'theme:edit-tags {theme? : O nome da pasta do tema (opcional)}';
    protected $description = 'Altera ou define as tags/categorias no theme.json de temas existentes';

    public function handle(): int
    {
        do {
            // 1. Localiza todos os temas instalados que possuem theme.json
            $themesPath = base_path('themes');
            if (!File::exists($themesPath)) {
                $this->error("O diretório 'themes/' não foi encontrado!");
                return Command::FAILURE;
            }

            $directories = File::directories($themesPath);
            $availableThemes = [];

            foreach ($directories as $dir) {
                $folderName = basename($dir);
                if (File::exists("{$dir}/theme.json")) {
                    $availableThemes[] = $folderName;
                }
            }

            if (empty($availableThemes)) {
                $this->warn('Nenhum tema com theme.json foi encontrado na pasta themes/.');
                return Command::SUCCESS;
            }

            // 2. Define qual tema será editado (pelo argumento ou escolha numerada)
            $themeFolder = $this->argument('theme');

            if (!$themeFolder || !in_array($themeFolder, $availableThemes)) {
                $themeChoices = [];
                $cnt = 1;
                foreach ($availableThemes as $t) {
                    $themeChoices[$cnt++] = $t;
                }

                $this->line('');
                $selectedFolder = $this->choice(
                    'Selecione qual tema deseja editar:',
                    $themeChoices,
                    1
                );

                $themeFolder = $selectedFolder;
            }

            $jsonPath = base_path("themes/{$themeFolder}/theme.json");
            $manifest = json_decode(File::get($jsonPath), true) ?? [];

            // 3. Exibe as tags atuais
            $currentTags = (array) ($manifest['tags'] ?? []);
            $this->line('');
            $this->info("==================================================");
            $this->info("Editando Tema: {$themeFolder}");
            $this->comment("Tags atuais: " . (empty($currentTags) ? 'Nenhuma' : implode(', ', $currentTags)));
            $this->info("==================================================");

            // 4. Monta as opções de categorias a partir do config('addons.tags.theme')
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

            // 5. Pergunta as novas tags
            $selectedLabels = $this->choice(
                'Selecione as novas tags do tema (digite os números separados por vírgula, ex: 1,3):',
                $choices,
                null,
                null,
                true // Permite seleção múltipla
            );

            // 6. Converte as seleções de volta para os slugs ('blog', 'corporate', etc.)
            $selectedTags = [];
            if (is_array($selectedLabels)) {
                foreach ($selectedLabels as $selectedText) {
                    if (isset($slugMap[$selectedText])) {
                        $selectedTags[] = $slugMap[$selectedText];
                    }
                }
            }

            // 7. Atualiza e salva o theme.json preservando todos os outros campos
            $manifest['tags'] = array_values(array_unique($selectedTags));

            File::put(
                $jsonPath,
                json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            );

            $this->info("✔ Tags do tema '{$themeFolder}' atualizadas com sucesso para: [" . implode(', ', $manifest['tags']) . "]");
            $this->line('');

            // Limpa o argumento para permitir escolher outro tema no próximo loop
            $this->input->setArgument('theme', null);

        } while ($this->confirm('Deseja editar as tags de outro tema?', true));

        $this->info('Finalizado!');
        return Command::SUCCESS;
    }
}
