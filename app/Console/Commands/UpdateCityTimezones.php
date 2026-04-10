<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateCityTimezones extends Command
{
    protected $signature = 'cities:update-timezones';
    protected $description = 'Atualiza timezones da tabela cities usando dados do GeoNames';

    public function handle()
    {
        $filePath = database_path('data/cities15000.txt');

        if (!file_exists($filePath)) {
            $this->error("Arquivo não encontrado: $filePath");
            return 1;
        }

        $this->info('📥 Processando arquivo...');
        $handle = fopen($filePath, 'r');

        $total = 0;
        $updated = 0;
        $ignored = 0;
        $notFound = 0;

        $bar = $this->output->createProgressBar();

        while (($line = fgets($handle)) !== false) {
            $total++;

            // Usa str_getcsv para lidar com tabulações variáveis
            $data = str_getcsv($line, "\t");

            // feature_class = 'P' (cidade)
            if (($data[6] ?? '') !== 'P') {
                $ignored++;
                continue;
            }

            $name = trim($data[1] ?? '');
            $iso2 = trim($data[8] ?? '');
            $timezone = trim($data[17] ?? '');

            if (!$name || !$iso2 || !$timezone) {
                $ignored++;
                continue;
            }

            $affected = DB::table('cities')
                ->where('city', $name)
                ->where('iso2', $iso2)
                ->update(['timezone' => $timezone]);

            if ($affected) {
                $updated++;
            } else {
                $notFound++;
            }

            if ($total % 1000 == 0) {
                $bar->advance(1000);
            }
        }

        fclose($handle);
        $bar->finish();

        $this->newLine(2);
        $this->info('✅ Processamento concluído!');
        $this->table(
            ['Total', 'Atualizadas', 'Ignoradas', 'Não encontradas'],
            [[$total, $updated, $ignored, $notFound]]
        );

        // Mostra cidades sem timezone
        $missing = DB::table('cities')
            ->whereNull('timezone')
            ->orWhere('timezone', '')
            ->count();

        if ($missing > 0) {
            $this->warn("⚠️  $missing cidades ainda estão sem timezone.");
            $this->line("   Para removê-las: php artisan cities:clean");
        } else {
            $this->info('✅ Todas as cidades têm timezone!');
        }

        return 0;
    }
}
