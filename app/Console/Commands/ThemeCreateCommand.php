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
            $themePath . '/assets',
            $themePath . '/resources/views/public/templates',
            $themePath . '/resources/views/public/post-templates'
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
            'screenshot' => 'assets/screenshot.png'
        ];
        File::put($themePath . '/theme.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        // 3. Criar template de exemplo (default)
        $defaultTemplate = "<?php\n\n?>\n@extends('public.site-layout')\n\n@section('content')\n<div class=\"container\">\n    <h1>{{ \$page->title }}</h1>\n    <div>{!! \$page->content !!}</div>\n</div>\n@endsection";
        File::put($themePath . '/resources/views/public/templates/default.blade.php', $defaultTemplate);

        $this->info("--------------------------------------------------");
        $this->info("Tema '{$studlyName}' criado com sucesso!");
        $this->warn("Path: themes/{$studlyName}");
        $this->info("--------------------------------------------------");

        return Command::SUCCESS;
    }
}
