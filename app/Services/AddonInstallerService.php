<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ZipArchive;
use Exception;

class AddonInstallerService
{
    /**
     * Baixa e descompacta um addon (plugin ou tema).
     */
    public function installFromUrl(string $name, string $downloadUrl, string $type = 'plugin'): bool
    {
        $folderName      = Str::studly($name);
        $baseFolder      = $type === 'theme' ? 'themes' : 'plugins';
        $tempZipPath     = storage_path("app/temp/{$folderName}.zip");
        $destinationPath = base_path("{$baseFolder}/{$folderName}");

        File::ensureDirectoryExists(storage_path('app/temp'));

        try {
            $response = Http::timeout(60)->get($downloadUrl);

            if ($response->failed()) {
                throw new Exception("Erro ao baixar o zip de: {$downloadUrl}");
            }

            File::put($tempZipPath, $response->body());

            $zip = new ZipArchive();
            if ($zip->open($tempZipPath) === true) {
                File::ensureDirectoryExists($destinationPath);
                $zip->extractTo($destinationPath);
                $zip->close();
            } else {
                throw new Exception("Não foi possível abrir o arquivo ZIP.");
            }

            $this->normalizeExtractedFolder($destinationPath);

            return true;
        } catch (Exception $e) {
            logger()->error("Erro na instalação do {$type} {$name}: " . $e->getMessage());
            return false;
        } finally {
            if (File::exists($tempZipPath)) {
                File::delete($tempZipPath);
            }
        }
    }

    private function normalizeExtractedFolder(string $destinationPath): void
    {
        $directories = File::directories($destinationPath);
        $files       = File::files($destinationPath);

        if (count($directories) === 1 && count($files) === 0) {
            $innerFolder = $directories[0];
            File::copyDirectory($innerFolder, $destinationPath);
            File::deleteDirectory($innerFolder);
        }
    }
}
