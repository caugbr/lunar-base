<?php

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

/**
 * Faz upload de uma imagem processando originais e variações (thumbnail/resize).
 *
 * Salva os arquivos no disco 'public' do Laravel e retorna paths relativos
 * para armazenamento no banco de dados, além das URLs públicas para exibição.
 *
 * @param \Illuminate\Http\UploadedFile $file Arquivo enviado via form
 * @param string $folder Subpasta dentro de media/ para organizar os arquivos (ex: 'settings', 'posts')
 * @param array $options Opções de processamento:
 *   - save_original (bool): Se deve salvar a imagem original sem alterações. Default: true
 *   - thumb (array|null): Gera thumbnail. Formato: [width, height, crop]. Ex: [150, 150, true] para crop quadrado
 *   - resize (int|null): Largura para redimensionamento proporcional (altura ajustada automaticamente)
 *   - quality (int): Qualidade de compressão JPEG/WEBP (1-100). Default: 80
 *   - format (string|null): Formato de saída para variações ('webp', 'jpg', etc). Null mantém o original
 *
 * @return array Array com paths e URLs gerados:
 *   - original: path relativo da imagem original (se save_original=true)
 *   - url: URL pública da imagem original
 *   - thumbnail: path relativo da thumbnail (se thumb configurado)
 *   - thumbnail_url: URL pública da thumbnail
 *   - resized: path relativo da versão redimensionada (se resize configurado)
 *   - resized_url: URL pública da versão redimensionada
 *
 * @example
 *   $result = uploadImage($request->file('logo'), 'settings', [
 *       'thumb' => [100, 100, true],
 *       'resize' => 800,
 *       'format' => 'webp'
 *   ]);
 *   // Salvar no banco: $setting->value = $result['original'];
 */
if (!function_exists('uploadImage')) {
    function uploadImage($file, $folder = 'images', $options = [])
    {
        $defaults = [
            'save_original' => true,
            'thumb' => null,        // [width, height, crop]
            'resize' => null,       // width apenas (mantém aspect ratio)
            'quality' => 80,
            'format' => null,       // ex: 'webp', null mantém original
        ];
        $options = array_merge($defaults, $options);

        $result = [];
        $extension = $file->getClientOriginalExtension();
        $fileName = time() . '_' . Str::uuid() . '.' . $extension;

        // Salva original primeiro para podermos reutilizar como base
        if ($options['save_original']) {
            $originalPath = $file->storeAs("media/{$folder}/original", $fileName, 'public');
            $result['original'] = $originalPath;
            $result['url'] = Storage::disk('public')->url($originalPath);
        }

        // Path temporário seguro para leitura (UploadedFile)
        $tempPath = $file->getRealPath();

        // --- GERA THUMBNAIL ---
        if ($options['thumb'] && is_array($options['thumb'])) {
            [$tWidth, $tHeight, $crop] = array_pad($options['thumb'], 3, false);

            $manager = new ImageManager(new Driver());
            $thumbImage = $manager->read($tempPath);

            if ($crop) {
                // cover() corta e redimensiona para preencher exatamente as dimensões
                $thumbImage->cover($tWidth, $tHeight);
            } else {
                // resize() mantém aspect ratio
                $thumbImage->resize($tWidth, $tHeight, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize(); // evita upscale
                });
            }

            $thumbName = pathinfo($fileName, PATHINFO_FILENAME) . '_thumb.' . ($options['format'] ?? $extension);
            $thumbPath = "media/{$folder}/cache/{$thumbName}";

            // v4: encode()->toString()
            Storage::disk('public')->put(
                $thumbPath,
                $thumbImage->encode($options['format'] ?? $extension, $options['quality'])->toString()
            );

            $result['thumbnail'] = $thumbPath;
            $result['thumbnail_url'] = Storage::disk('public')->url($thumbPath);
        }

        // --- GERA VERSÃO REDIMENSIONADA ---
        if ($options['resize']) {
            $manager = new ImageManager(new Driver());
            $resizeImage = $manager->read($tempPath);

            $resizeImage->resize($options['resize'], null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            $resizeName = pathinfo($fileName, PATHINFO_FILENAME) . '_resized.' . ($options['format'] ?? $extension);
            $resizePath = "media/{$folder}/cache/{$resizeName}";

            Storage::disk('public')->put(
                $resizePath,
                $resizeImage->encode($options['format'] ?? $extension, $options['quality'])->toString()
            );

            $result['resized'] = $resizePath;
            $result['resized_url'] = Storage::disk('public')->url($resizePath);
        }

        return $result;
    }
}

/**
 * Converte path relativo de armazenamento em URL pública.
 *
 * @param string|null $path Path relativo (ex: "media/settings/original/img.jpg")
 * @param string $disk Disco do Laravel (default: 'public')
 * @return string|null URL completa para uso em <img src=""> ou null se não existir
 */
if (!function_exists('getImage')) {
    function getImage($path, $disk = 'public')
    {
        if (!$path) return null;

        // Se já for URL completa, retorna
        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        return Storage::disk($disk)->exists($path)
            ? Storage::disk($disk)->url($path)
            : null;
    }
}

/**
 * Deleta uma imagem e suas variações em cache (thumbnail, resized) do disco.
 *
 * Remove o arquivo principal e busca automaticamente por variações com sufixos
 * '_thumb' e '_resized' no mesmo diretório de cache, garantindo limpeza completa.
 *
 * @param string|null $path Path relativo da imagem principal (ex: "media/settings/original/img.jpg")
 * @param string $folder Pasta base usada no upload original, para localizar o cache. Default: 'images'
 * @param string $disk Disco do Laravel onde os arquivos estão armazenados. Default: 'public'
 *
 * @return bool True se pelo menos um arquivo foi deletado, false se o path era nulo ou nenhum arquivo existia
 *
 * @example
 *   // Deletar imagem de configuração
 *   deleteImage($setting->value, 'settings');
 *
 *   // Deletar com disco personalizado (ex: S3)
 *   deleteImage($path, 'posts', 's3');
 */
if (!function_exists('deleteImage')) {
    function deleteImage($path, $folder = 'images', $disk = 'public')
    {
        if (!$path) return false;

        $deleted = Storage::disk($disk)->delete($path);

        // Tenta deletar variações no cache pelo nome do arquivo
        $filename = basename($path);
        $dirname = dirname($path);
        $cacheDir = "media/{$folder}/cache";

        // Deleta possíveis variações (_thumb, _resized)
        foreach (['_thumb', '_resized'] as $suffix) {
            $info = pathinfo($filename);
            $variantName = $info['filename'] . $suffix . '.' . $info['extension'];
            $variantPath = "{$cacheDir}/{$variantName}";

            if (Storage::disk($disk)->exists($variantPath)) {
                Storage::disk($disk)->delete($variantPath);
            }
        }

        return $deleted;
    }
}
