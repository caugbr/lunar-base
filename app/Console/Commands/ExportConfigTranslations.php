<?php

namespace App\Console\Commands;

use App\Support\ConfigTranslator;
use Illuminate\Console\Command;

class ExportConfigTranslations extends Command
{
    protected $signature = 'config:lang {locale : O código do idioma (ex: en, es, fr)}';
    protected $description = 'Extrai termos dos configs e gera o arquivo de tradução JSON em /lang';

    public function handle(): int
    {
        $locale = strtolower($this->argument('locale'));

        $this->info("Extraindo termos das configurações para: [{$locale}]...");

        $result = ConfigTranslator::exportJson($locale);

        $this->info("✓ Arquivo gerado com sucesso em:");
        $this->line("  " . $result['file']);
        $this->line("  Total de termos: {$result['total']}");
        $this->line("  Novos adicionados: {$result['added']}");

        return self::SUCCESS;
    }
}
