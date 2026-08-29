<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class FakeMigrate extends Command
{
    protected $signature = 'migrate:fake {--path= : Caminho customizado para a pasta de migrations}';
    protected $description = 'Marca migrations existentes como executadas no banco sem rodá-las';

    public function handle()
    {
        $path = $this->option('path')
            ? base_path($this->option('path'))
            : database_path('migrations');

        if (!File::exists($path)) {
            $this->error("Caminho não encontrado: {$path}");
            return 1;
        }

        // Pega o maior batch atual
        $lastBatch = DB::table('migrations')->max('batch') ?? 0;
        $newBatch = $lastBatch + 1;

        // Migrations já registradas no banco
        $ranMigrations = DB::table('migrations')->pluck('migration')->toArray();

        $files = File::files($path);
        $added = 0;

        foreach ($files as $file) {
            $filename = pathinfo($file->getFilename(), PATHINFO_FILENAME);

            if (!in_array($filename, $ranMigrations) && str_ends_with($file->getFilename(), '.php')) {
                DB::table('migrations')->insert([
                    'migration' => $filename,
                    'batch' => $newBatch,
                ]);

                $this->info("Marcada como executada: {$filename}");
                $added++;
            }
        }

        if ($added === 0) {
            $this->info('Nenhuma nova migration pendente para marcar.');
        } else {
            $this->info("Pronto! {$added} migrations foram marcadas no banco com sucesso.");
        }

        return 0;
    }
}
