<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PluginEditTags extends Command
{
    protected $signature = 'plugin:edit-tags {plugin? : O nome da pasta do plugin (opcional)}';
    protected $description = 'Altera ou define as tags/categorias no plugin.json de plugins existentes';

    public function handle(): int
    {
        do {
            // 1. Localiza todos os plugins instalados que possuem plugin.json
            $pluginsPath = base_path('plugins');
            if (!File::exists($pluginsPath)) {
                $this->error("O diretório 'plugins/' não foi encontrado!");
                return Command::FAILURE;
            }

            $directories = File::directories($pluginsPath);
            $availablePlugins = [];

            foreach ($directories as $dir) {
                $folderName = basename($dir);
                if (File::exists("{$dir}/plugin.json")) {
                    $availablePlugins[] = $folderName;
                }
            }

            if (empty($availablePlugins)) {
                $this->warn('Nenhum plugin com plugin.json foi encontrado na pasta plugins/.');
                return Command::SUCCESS;
            }

            // 2. Define qual plugin será editado (pelo argumento ou escolha numerada)
            $pluginFolder = $this->argument('plugin');

            if (!$pluginFolder || !in_array($pluginFolder, $availablePlugins)) {
                $pluginChoices = [];
                $cnt = 1;
                foreach ($availablePlugins as $p) {
                    $pluginChoices[$cnt++] = $p;
                }

                $this->line('');
                $selectedFolder = $this->choice(
                    'Selecione qual plugin deseja editar:',
                    $pluginChoices,
                    1
                );

                $pluginFolder = $selectedFolder;
            }

            $jsonPath = base_path("plugins/{$pluginFolder}/plugin.json");
            $manifest = json_decode(File::get($jsonPath), true) ?? [];

            // 3. Exibe as tags atuais
            $currentTags = (array) ($manifest['tags'] ?? []);
            $this->line('');
            $this->info("==================================================");
            $this->info("Editando: {$pluginFolder}");
            $this->comment("Tags atuais: " . (empty($currentTags) ? 'Nenhuma' : implode(', ', $currentTags)));
            $this->info("==================================================");

            // 4. Monta as opções de categorias a partir do config('pluginSettings.categories')
            $categoriesConfig = config('pluginSettings.categories', []);
            $choices = [];
            $slugMap = [];

            $cnt = 1;
            foreach ($categoriesConfig as $slug => $category) {
                $label   = $category['name'] ?? ucfirst($slug);
                $desc    = !empty($category['description']) ? " - {$category['description']}" : "";
                $display = $label . $desc;

                $choices[$cnt]     = $display;
                $slugMap[$display] = $slug;
                $cnt++;
            }

            // 5. Pergunta as novas tags
            $selectedLabels = $this->choice(
                'Selecione as novas tags (digite os números separados por vírgula, ex: 1,3):',
                $choices,
                null,
                null,
                true // Permite seleção múltipla
            );

            // 6. Converte as seleções de volta para os slugs
            $selectedTags = [];
            if (is_array($selectedLabels)) {
                foreach ($selectedLabels as $selectedText) {
                    if (isset($slugMap[$selectedText])) {
                        $selectedTags[] = $slugMap[$selectedText];
                    }
                }
            }

            // 7. Atualiza e salva o plugin.json preservando os outros dados
            $manifest['tags'] = array_values(array_unique($selectedTags));

            File::put(
                $jsonPath,
                json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            );

            $this->info("✔ Tags de '{$pluginFolder}' atualizadas com sucesso para: [" . implode(', ', $manifest['tags']) . "]");
            $this->line('');

            // Limpa o argumento para que no próximo loop ele liste os plugins novamente
            $this->input->setArgument('plugin', null);

        } while ($this->confirm('Deseja editar as tags de outro plugin?', true));

        $this->info('Finalizado!');
        return Command::SUCCESS;
    }
}
