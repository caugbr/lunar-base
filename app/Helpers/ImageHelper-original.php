<?php

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Faz upload de uma imagem SALVANDO APENAS O ORIGINAL.
 *
 * Processamento de thumbnail/resize foi temporariamente removido para estabilidade.
 * Pode ser reintegrado depois via serviço separado.
 *
 * @param \Illuminate\Http\UploadedFile $file
 * @param string $folder
 * @param array $options
 * @return array
 */
if (!function_exists('uploadImage')) {

    function uploadImage($file, $folder = 'images', $options = [])
    {
        $defaults = [
            'save_original' => true,
            'thumb' => null,        // ⚠️ Ignorado nesta versão estável
            'resize' => null,       // ⚠️ Ignorado nesta versão estável
            'quality' => 80,
            'format' => null,
            'create_media_record' => true,
            'mediaable' => null,
            'alt' => null,
            'caption' => null,
        ];
        $options = array_merge($defaults, $options);

        $result = [];
        $extension = $file->getClientOriginalExtension();
        $fileName = time() . '_' . Str::uuid() . '.' . $extension;
        $tempPath = $file->getRealPath();
        $mimeType = $file->getMimeType();
        $fileSize = $file->getSize();

        // Salva original (Laravel Storage puro - 100% estável)
        if ($options['save_original']) {
            $originalPath = $file->storeAs("media/{$folder}/original", $fileName, 'public');
            $result['original'] = $originalPath;
            $result['url'] = Storage::disk('public')->url($originalPath);
        }

        // 🗄️ Cria registro no banco (se ativado)
        if ($options['create_media_record'] && isset($result['original'])) {
            // Extrai dimensões: SVG via XML, imagens raster via getimagesize
            $width = null;
            $height = null;

            if (str_starts_with($mimeType, 'image/svg')) {
                $svgContent = file_get_contents($tempPath);
                if (preg_match('/<svg[^>]*\sviewBox=["\']?([\d\s\.,]+)["\']?/i', $svgContent, $matches)) {
                    $viewBox = preg_split('/[\s,]+/', trim($matches[1]));
                    $width = (int) ($viewBox[2] ?? null);
                    $height = (int) ($viewBox[3] ?? null);
                }
                if (!$width && preg_match('/<svg[^>]*\swidth=["\']?([\d\.]+)/i', $svgContent, $w)) {
                    $width = (int) $w[1];
                }
                if (!$height && preg_match('/<svg[^>]*\sheight=["\']?([\d\.]+)/i', $svgContent, $h)) {
                    $height = (int) $h[1];
                }
            } elseif (function_exists('getimagesize') && str_starts_with($mimeType, 'image/')) {
                $size = @getimagesize($tempPath);
                if ($size) {
                    $width = $size[0];
                    $height = $size[1];
                }
            }

            $mediaData = [
                'name' => $file->getClientOriginalName(),
                'path' => $result['original'],
                'mime_type' => $mimeType,
                'size' => $fileSize,
                'width' => $width,
                'height' => $height,
                'alt' => $options['alt'],
                'caption' => $options['caption'],
            ];

            $media = \App\Models\Media::create($mediaData);

            if ($options['mediaable'] instanceof \Illuminate\Database\Eloquent\Model) {
                $media->mediaable()->associate($options['mediaable'])->save();
            }

            $result['media'] = $media;
            $result['media_id'] = $media->id;
        }

        // 🖼️ Gera thumbnail automaticamente com base nas configurações
        // SVG não recebe thumbnail (escala infinitamente)
        if (!str_starts_with($mimeType, 'image/svg')) {
            generateMediaThumbnail($result['original'], $folder, settingsGroup('media'));
        }

        return $result;
    }
}

/**
 * Converte path relativo em URL pública
 */
if (!function_exists('getImage')) {
    function getImage($path, $disk = 'public')
    {
        if (!$path) return null;
        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }
        return Storage::disk($disk)->exists($path)
            ? Storage::disk($disk)->url($path)
            : null;
    }
}

/**
 * Deleta imagem e suas variações (se existirem)
 */
if (!function_exists('deleteImage')) {
    function deleteImage($path, $folder = 'images', $disk = 'public')
    {
        if (!$path) return false;

        $deleted = Storage::disk($disk)->delete($path);

        // 🗑️ Limpa variações via helper dedicado
        deleteMediaVariants($path, $folder);

        return $deleted;
    }
}
