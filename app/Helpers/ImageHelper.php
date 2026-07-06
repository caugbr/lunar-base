<?php

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;

if (!function_exists('uploadImage')) {

    function uploadImage($file, $folder = 'images', $options = [])
    {
        $defaults = [
            'save_original' => true,
            'quality' => 80,
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

        // Define o caminho onde o original será salvo
        $relDir = "media/{$folder}/original";
        $originalPath = "{$relDir}/{$fileName}";

        // Salva original
        if ($options['save_original']) {

            // Verificamos: compressão ligada? É imagem? NÃO é SVG?
            if (setting('media.media_auto_compress', true) &&
                str_starts_with($mimeType, 'image/') &&
                !str_contains($mimeType, 'svg')) {

                try {
                    $img = ImageManager::gd()->read($file);

                    // Otimiza o original: garante que não passe de 2000px (limite sênior para web)
                    if ($img->width() > 2000) {
                        $img->scale(width: 2000);
                    }

                    $quality = (int) setting('media.media_quality', 80);
                    $format = setting('media.media_formats', 'webp');

                    $encoded = match($format) {
                        'webp' => $img->toWebp($quality),
                        'png'  => $img->toPng(),
                        default => $img->toJpeg($quality),
                    };

                    Storage::disk('public')->put($originalPath, (string) $encoded);

                    // Atualiza o tamanho do arquivo após a compressão
                    $fileSize = strlen((string)$encoded);

                } catch (\Exception $e) {
                    \Log::error("Falha na compressão do original: " . $e->getMessage());
                    // Fallback: se a compressão falhar, salva o arquivo bruto para não perder o upload
                    $file->storeAs($relDir, $fileName, 'public');
                }
            } else {
                // Se estiver desligada ou for SVG/Documento, salva o arquivo bruto
                $file->storeAs($relDir, $fileName, 'public');
            }

            $result['original'] = $originalPath;
            $result['url'] = Storage::disk('public')->url($originalPath);
        }

        // Cria registro no banco (se ativado)
        if ($options['create_media_record'] && isset($result['original'])) {
            // Extrai dimensões (mesma lógica que você já tinha)
            $width = null;
            $height = null;

            if (str_starts_with($mimeType, 'image/svg')) {
                $svgContent = file_get_contents($tempPath);
                if (preg_match('/<svg[^>]*\sviewBox=["\']?([\d\s\.,]+)["\']?/i', $svgContent, $matches)) {
                    $viewBox = preg_split('/[\s,]+/', trim($matches[1]));
                    $width = (int) ($viewBox[2] ?? null);
                    $height = (int) ($viewBox[3] ?? null);
                }
            } elseif (function_exists('getimagesize') && str_starts_with($mimeType, 'image/')) {
                $size = @getimagesize($tempPath);
                if ($size) {
                    $width = $size[0];
                    $height = $size[1];
                }
            }

            $media = \App\Models\Media::create([
                'name' => $file->getClientOriginalName(),
                'path' => $result['original'],
                'mime_type' => $mimeType,
                'size' => $fileSize,
                'width' => $width,
                'height' => $height,
                'alt' => $options['alt'],
                'caption' => $options['caption'],
            ]);

            if ($options['mediaable'] instanceof \Illuminate\Database\Eloquent\Model) {
                $media->mediaable()->associate($options['mediaable'])->save();
            }

            $result['media'] = $media;
            $result['media_id'] = $media->id;
        }

        // Gera thumbnail
        if (str_starts_with($mimeType, 'image/') && !str_contains($mimeType, 'svg')) {
            // generateMediaThumbnail($result['original'], $folder, settingsGroup('media'));
            generateMediaVariants($result['original'], $folder, settingsGroup('media'));
        }

        return $result;
    }
}
