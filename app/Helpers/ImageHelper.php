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

/**
 * Remove fisicamente uma imagem e todas as suas variações em cache
 */
if (!function_exists('deleteImage')) {
    function deleteImage($path, $folder = 'images', $disk = 'public')
    {
        if (!empty($path)) {
            // Remove o arquivo original físico no disco correspondente (padrão 'public')
            if (Storage::disk($disk)->exists($path)) {
                Storage::disk($disk)->delete($path);

                // Aciona o limpador do ImageProcessor para apagar as variações em cache (_thumb, _large, etc.)
                if (function_exists('deleteMediaVariants')) {
                    deleteMediaVariants($path, $folder);
                }
            }
        }
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

if (! function_exists('addImageSize')) {
    /**
     * Registra um novo tamanho de imagem na configuração em tempo de execução.
     *
     * @param string      $name     Identificador único do tamanho (ex: 'thumbnail_card', 'banner')
     * @param int         $width    Largura máxima em pixels
     * @param int|null    $height   Altura máxima em pixels (opcional)
     * @param bool        $crop     Se deve recortar exatamente nas dimensões especificadas
     * @param string      $position Posição do corte caso $crop seja true ('center', 'top', 'bottom')
     * @return void
     */
    function addImageSize(string $name, int $width, ?int $height = null, bool $crop = false, string $position = 'center'): void
    {
        // Injeta ou sobrescreve dinamicamente no config('imageSizes')
        config([
            "imageSizes.{$name}" => [
                'width'    => $width,
                'height'   => $height,
                'crop'     => $crop,
                'position' => $position,
            ]
        ]);
    }
}

if (!function_exists('saveBase64Image')) {
    /**
     * Converte uma string Base64 em arquivo físico no Storage e devolve a URL pública.
     *
     * @param string|null $base64String String no formato "data:image/png;base64,..." ou URL normal.
     * @param string $folder Subpasta dentro de /media (ex: 'snippets', 'avatars', 'temp').
     * @param string $disk Disco do Storage (padrão: 'public').
     * @return string|null URL pública da imagem salva ou a própria URL se não for Base64.
     */
    function saveBase64Image(?string $base64String, string $folder = 'snippets', string $disk = 'public'): ?string
    {
        if (empty($base64String)) {
            return null;
        }

        // Se já for uma URL normal salva anteriormente (http://, /storage/...), não faz nada
        if (!str_starts_with($base64String, 'data:image')) {
            return $base64String;
        }

        try {
            // Extrai a extensão da imagem a partir do cabeçalho MIME
            // Ex: data:image/png;base64,... -> png
            preg_match('/^data:image\/(\w+);base64,/', $base64String, $matches);
            $extension = $matches[1] ?? 'jpg';
            if ($extension === 'jpeg') $extension = 'jpg';

            // Extrai e decodifica os bytes binários da imagem
            $imageData = substr($base64String, strpos($base64String, ',') + 1);
            $decodedData = base64_decode($imageData);

            if ($decodedData === false) {
                return null;
            }

            // Gera o nome do arquivo seguindo o padrão nativo do seu sistema
            $fileName = time() . '_' . \Illuminate\Support\Str::uuid() . '.' . $extension;
            $relativePath = "media/{$folder}/original/{$fileName}";

            // Grava fisicamente no disco configurado
            \Illuminate\Support\Facades\Storage::disk($disk)->put($relativePath, $decodedData);

            // Opcional: Se quiser gerar as miniaturas em cache usando o seu generateMediaVariants:
            if (function_exists('generateMediaVariants')) {
                generateMediaVariants($relativePath, $folder, settingsGroup('media'));
            }

            // Retorna a URL pública limpa (ex: /storage/media/snippets/original/...)
            return \Illuminate\Support\Facades\Storage::disk($disk)->url($relativePath);

        } catch (\Exception $e) {
            \Log::error("Erro ao converter e salvar imagem Base64: " . $e->getMessage());
            return null;
        }
    }
}
