<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportCities extends Command
{
    protected $signature = 'cities:import {file?}';
    protected $description = 'Importa cidades do CSV do Simplemaps';

    public function handle()
    {
        $file = $this->argument('file') ?? database_path('data/worldcities.csv');

        if (!file_exists($file)) {
            $this->error("Arquivo não encontrado: $file");
            return 1;
        }

        $this->info("Importando cidades de: $file");
        $this->newLine();

        $handle = fopen($file, 'r');
        $header = fgetcsv($handle); // pula cabeçalho

        $count = 0;
        $bar = $this->output->createProgressBar(48000);

        while (($row = fgetcsv($handle)) !== false) {
            DB::table('cities')->insert([
                'city' => $row[0],
                'city_ascii' => $row[1],
                'lat' => $row[2],
                'lng' => $row[3],
                'country' => $row[4],
                'iso2' => $row[5],
                'iso3' => $row[6],
                'admin_name' => $row[7],
                'capital' => $row[8],
                'population' => $row[9] ?: null,
                'timezone' => $row[10],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $count++;
            $bar->advance();
        }

        fclose($handle);
        $bar->finish();

        $this->newLine();
        $this->info("✅ $count cidades importadas com sucesso!");
    }
}
