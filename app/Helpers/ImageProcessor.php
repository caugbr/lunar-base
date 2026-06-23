<?php

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

/**
 * Gera thumbnail baseado nas configurações.
 * Versão otimizada com Intervention Image.
 */
if (!function_exists('generateMediaThumbnail')) {
    function generateMediaThumbnail($originalPath, $folder, $settings = [])
    {
        if (str_ends_with(strtolower($originalPath), '.svg')) {
            return false;
        }

        $width    = (int) ($settings['media_thumbnail_width'] ?? 300);
        $height   = (int) ($settings['media_thumbnail_height'] ?? 300);
        $crop     = (bool) ($settings['media_crop_thumbnail'] ?? false);
        $position = $settings['media_crop_position'] ?? 'center';
        $quality  = max(1, min(100, (int) ($settings['media_quality'] ?? 80)));
        $format   = strtolower($settings['media_formats'] ?? 'webp');

        if ($width <= 0 || $height <= 0) return false;

        $fullPath = storage_path('app/public/' . $originalPath);
        if (!file_exists($fullPath)) return false;

        $pathInfo = pathinfo($originalPath);
        $ext = in_array($format, ['jpeg', 'jpg', 'png', 'webp']) ? $format : $pathInfo['extension'];

        $cacheDir = "media/{$folder}/cache";
        $thumbName = $pathInfo['filename'] . '_thumb.' . $ext;
        $thumbRelPath = "{$cacheDir}/{$thumbName}";
        $thumbFullPath = storage_path('app/public/' . $thumbRelPath);

        if (!file_exists(dirname($thumbFullPath))) {
            mkdir(dirname($thumbFullPath), 0755, true);
        }

        // Chamada da função que faz o trabalho pesado
        return processImageWithIntervention($fullPath, $thumbFullPath, $width, $height, $crop, $position, $quality, $ext)
            ? $thumbRelPath
            : false;
    }
}

/**
 * Processamento com Intervention Image: muito mais eficiente e legível que GD puro.
 */
// if (!function_exists('processImageWithIntervention')) {
//     function processImageWithIntervention($source, $destination, $width, $height, $crop, $position, $quality, $format)
//     {
//         try {
//             // Cria o gerenciador com o driver GD (padrão de servidores comuns)
//             $manager = new ImageManager(new Driver());

//             // Lendo a imagem
//             $image = $manager->read($source);

//             if ($crop) {
//                 // O método cover() faz o que seu código antigo fazia:
//                 // Redimensiona e corta para preencher o espaço (crop centralizado ou posicionado)
//                 // Posições suportadas: top, bottom, center, left, right...
//                 $image->cover($width, $height, $position);
//             } else {
//                 // Redimensiona proporcionalmente sem cortar
//                 $image->scale(width: $width, height: $height);
//             }

//             // Codifica no formato e qualidade desejados
//             $encoded = match($format) {
//                 'png'  => $image->toPng(),
//                 'webp' => $image->toWebp($quality),
//                 default => $image->toJpeg($quality),
//             };

//             // Salva no destino
//             $encoded->save($destination);

//             return true;
//         } catch (\Exception $e) {
//             \Log::error("Erro no processamento de imagem: " . $e->getMessage());
//             return false;
//         }
//     }
// }
if (!function_exists('processImageWithIntervention')) {
    function processImageWithIntervention($source, $destination, $width, $height, $crop, $position, $quality, $format)
    {
        try {
            // No v3, criamos o manager com o driver e lemos a imagem
            $manager = new ImageManager(new Driver());
            $image = $manager->read($source);

            if ($crop) {
                // cover() redimensiona e corta para preencher
                $image->cover($width, $height, $position);
            } else {
                // scale() apenas redimensiona proporcionalmente
                $image->scale(width: $width, height: $height);
            }

            // Codificação (PHP 8.3 match)
            $encoded = match($format) {
                'png'  => $image->toPng(),
                'webp' => $image->toWebp($quality),
                default => $image->toJpeg($quality),
            };

            $encoded->save($destination);
            return true;

        } catch (\Exception $e) {
            \Log::error("Erro no Intervention v3: " . $e->getMessage());
            return false;
        }
    }
}

// Mantivemos a função de deletar variantes como estava, pois ela apenas lida com arquivos.
if (!function_exists('deleteMediaVariants')) {
    function deleteMediaVariants($originalPath, $folder)
    {
        if (str_ends_with(strtolower($originalPath), '.svg')) return true;

        $cacheDir = storage_path("app/public/media/{$folder}/cache");
        $filename = basename($originalPath);
        $nameInfo = pathinfo($filename);

        $pattern = $cacheDir . '/' . $nameInfo['filename'] . '_thumb.*';
        $variants = @glob($pattern);

        if (!$variants) return true;

        foreach ($variants as $variant) {
            if (file_exists($variant)) @unlink($variant);
        }
        return true;
    }
}



// use Intervention\Image\ImageManager;
// use Intervention\Image\Drivers\Gd\Driver;

// /**
//  * Gera thumbnail baseado nas configurações.
//  */
// if (!function_exists('generateMediaThumbnail')) {
//     function generateMediaThumbnail($originalPath, $folder, $settings = [])
//     {
//         if (str_ends_with(strtolower($originalPath), '.svg')) {
//             return false;
//         }

//         $width    = (int) ($settings['media_thumbnail_width'] ?? 300);
//         $height   = (int) ($settings['media_thumbnail_height'] ?? 300);
//         $crop     = (bool) ($settings['media_crop_thumbnail'] ?? false);
//         $position = $settings['media_crop_position'] ?? 'center';
//         $quality  = max(1, min(100, (int) ($settings['media_quality'] ?? 80)));
//         $format   = strtolower($settings['media_formats'] ?? 'webp');

//         if ($width <= 0 || $height <= 0) return false;

//         $fullPath = storage_path('app/public/' . $originalPath);
//         if (!file_exists($fullPath)) return false;

//         $pathInfo = pathinfo($originalPath);
//         $ext = in_array($format, ['jpeg', 'jpg', 'png', 'webp']) ? $format : $pathInfo['extension'];

//         $cacheDir = "media/{$folder}/cache";
//         $thumbName = $pathInfo['filename'] . '_thumb.' . $ext;
//         $thumbRelPath = "{$cacheDir}/{$thumbName}";
//         $thumbFullPath = storage_path('app/public/' . $thumbRelPath);

//         if (!file_exists(dirname($thumbFullPath))) {
//             mkdir(dirname($thumbFullPath), 0755, true);
//         }

//         return processImageWithIntervention($fullPath, $thumbFullPath, $width, $height, $crop, $position, $quality, $ext)
//             ? $thumbRelPath
//             : false;
//     }
// }

// /**
//  * Processamento com Intervention Image v3
//  */
// if (!function_exists('processImageWithIntervention')) {
//     function processImageWithIntervention($source, $destination, $width, $height, $crop, $position, $quality, $format)
//     {
//         try {
//             $manager = new ImageManager(new Driver());
//             $image = $manager->read($source);

//             if ($crop) {
//                 // No v3, o cover aceita a posição como string (center, top, etc)
//                 $image->cover($width, $height, $position);
//             } else {
//                 $image->scale(width: $width, height: $height);
//             }

//             // Codificação e salvamento
//             switch ($format) {
//                 case 'png':
//                     $encoded = $image->toPng();
//                     break;
//                 case 'webp':
//                     $encoded = $image->toWebp($quality);
//                     break;
//                 default:
//                     $encoded = $image->toJpeg($quality);
//                     break;
//             }

//             $encoded->save($destination);
//             return true;
//         } catch (\Exception $e) {
//             \Log::error("Erro no processamento de imagem: " . $e->getMessage());
//             return false;
//         }
//     }
// }

// if (!function_exists('deleteMediaVariants')) {
//     function deleteMediaVariants($originalPath, $folder)
//     {
//         if (str_ends_with(strtolower($originalPath), '.svg')) return true;

//         $cacheDir = storage_path("app/public/media/{$folder}/cache");
//         $filename = basename($originalPath);
//         $nameInfo = pathinfo($filename);

//         $pattern = $cacheDir . '/' . $nameInfo['filename'] . '_thumb.*';
//         $variants = @glob($pattern);

//         if (!$variants) return true;

//         foreach ($variants as $variant) {
//             if (file_exists($variant)) @unlink($variant);
//         }
//         return true;
//     }
// }
