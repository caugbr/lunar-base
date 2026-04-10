<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanCitiesWithoutTimezone extends Command
{
    protected $signature = 'cities:clean';
    protected $description = 'Remove cidades sem timezone da tabela';

    public function handle()
    {
        $deleted = DB::table('cities')
            ->where('timezone', 'REGEXP', '^[0-9]+$')  // timezone é numérico (população)
            ->delete();

        $this->info("✅ $deleted cidades com timezone inválido removidas.");
        return 0;
    }
}
