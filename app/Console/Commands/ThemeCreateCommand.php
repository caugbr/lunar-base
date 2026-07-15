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
            $themePath . '/resources/assets/js',
            $themePath . '/resources/views/public/page-templates',
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
            'screenshot' => 'resources/assets/images/screenshot.png'
        ];
        File::put($themePath . '/theme.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        // 3. Criar template de exemplo (default)
        $defaultTemplate = "<?php\n\n?>\n@extends('public.site-layout')\n\n@section('content')\n<div class=\"container\">\n    <h1>{{ \$page->title }}</h1>\n    <div>{!! \$page->content !!}</div>\n</div>\n@endsection";
        File::put($themePath . '/resources/views/public/templates/default.blade.php', $defaultTemplate);

        $this->info("--------------------------------------------------");
        $this->info("Tema '{$studlyName}' criado com sucesso!");
        $this->warn("Path: themes/{$studlyName}");
        $this->info("--------------------------------------------------");

        $this->createPublicAssetLink($kebabName, $themePath);

        return Command::SUCCESS;
    }

    protected function createPublicAssetLink(string $kebabName, string $themePath): void
    {
        $publicThemesPath = public_path('themes');
        File::ensureDirectoryExists($publicThemesPath, 0755, true);

        $target = $themePath . '/resources/assets';
        $link = $publicThemesPath . '/' . $kebabName;

        if (File::exists($link)) return;

        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

        if ($isWindows) {
            exec("mklink /J " . escapeshellarg(str_replace('/', '\\', $link)) . " " . escapeshellarg(str_replace('/', '\\', $target)), $output, $returnVar);

            if ($returnVar !== 0) {
                $this->error("Erro crítico: Não foi possível criar a junction do Windows.");
                $this->error("Tente abrir o terminal como Administrador.");
                die();
            }
        } else {
            symlink($target, $link);
        }

        $this->info("Link criado com sucesso: public/themes/{$kebabName}");
    }
}
