<?php

/**
 * Gera thumbnail baseado nas configurações do grupo 'media'.
 * Lógica simplificada: crop só no eixo vertical (top/center/bottom), horizontal sempre centralizado.
 */
if (!function_exists('generateMediaThumbnail')) {
    function generateMediaThumbnail($originalPath, $folder, $settings = [])
    {
        $width    = (int) ($settings['media_thumbnail_width'] ?? 100);
        $height   = (int) ($settings['media_thumbnail_height'] ?? 100);
        $crop     = (bool) ($settings['media_crop_thumbnail'] ?? false);
        $position = $settings['media_crop_position'] ?? 'center'; // Simplificado: top | center | bottom
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

        return processImageGD($fullPath, $thumbFullPath, $width, $height, $crop, $position, $quality, $ext)
            ? $thumbRelPath
            : false;
    }
}

/**
 * Processamento com GD: redimensionamento proporcional + crop simplificado (3 posições verticais)
 */
if (!function_exists('processImageGD')) {
    function processImageGD($source, $destination, $width, $height, $crop, $position, $quality, $format)
    {
        [$srcW, $srcH, $type] = @getimagesize($source);
        if (!$srcW || !$srcH) return false;

        $srcImg = match($type) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($source),
            IMAGETYPE_PNG  => imagecreatefrompng($source),
            IMAGETYPE_GIF  => imagecreatefromgif($source),
            IMAGETYPE_WEBP => imagecreatefromwebp($source),
            default        => false
        };
        if (!$srcImg) return false;

        $dstImg = imagecreatetruecolor($width, $height);

        // Fundo: branco para JPEG, transparente para PNG/WEBP
        if (in_array($format, ['jpeg', 'jpg'])) {
            $bg = imagecolorallocate($dstImg, 255, 255, 255);
            imagefilledrectangle($dstImg, 0, 0, $width, $height, $bg);
        } else {
            imagealphablending($dstImg, false);
            imagesavealpha($dstImg, true);
            $transparent = imagecolorallocatealpha($dstImg, 255, 255, 255, 127);
            imagefilledrectangle($dstImg, 0, 0, $width, $height, $transparent);
        }

        if ($crop) {
            // 🟦 COVER: escala para preencher, corta o excesso
            $scale = max($width / $srcW, $height / $srcH);
            $scaledW = $srcW * $scale;
            $scaledH = $srcH * $scale;

            // Posição vertical simplificada (horizontal sempre 0.5 = centro)
            $posY = match($position) {
                'top' => 0.0,
                'bottom' => 1.0,
                default => 0.5, // center
            };
            $posX = 0.5; // Sempre centralizado horizontalmente

            // Calcula coordenadas na imagem escalada
            $cropX_scaled = ($scaledW - $width) * $posX;
            $cropY_scaled = ($scaledH - $height) * $posY;

            // Mapeia de volta para coordenadas REAIS da imagem original
            $src_x = (int)($cropX_scaled / $scale);
            $src_y = (int)($cropY_scaled / $scale);
            $src_w = (int)($width / $scale);
            $src_h = (int)($height / $scale);

            // Garante limites seguros
            $src_x = max(0, min($src_x, $srcW - $src_w));
            $src_y = max(0, min($src_y, $srcH - $src_h));

            imagecopyresampled($dstImg, $srcImg, 0, 0, $src_x, $src_y, $width, $height, $src_w, $src_h);
        } else {
            // 🟩 CONTAIN: escala proporcional para caber no box, centraliza
            $scale = min($width / $srcW, $height / $srcH);
            $newW = (int)($srcW * $scale);
            $newH = (int)($srcH * $scale);

            $dstX = (int)(($width - $newW) / 2);
            $dstY = (int)(($height - $newH) / 2);

            imagecopyresampled($dstImg, $srcImg, $dstX, $dstY, 0, 0, $newW, $newH, $srcW, $srcH);
        }

        // Salva no formato/qualidade definidos
        $saved = match($format) {
            'jpeg', 'jpg' => imagejpeg($dstImg, $destination, $quality),
            'png'         => imagepng($dstImg, $destination, max(0, min(9, 9 - intdiv($quality, 10)))),
            'webp'        => imagewebp($dstImg, $destination, $quality),
            default       => imagejpeg($dstImg, $destination, $quality)
        };

        imagedestroy($srcImg);
        imagedestroy($dstImg);

        return $saved && file_exists($destination);
    }
}

/**
 * Deleta variações (thumbnails) associadas a um arquivo original.
 */
if (!function_exists('deleteMediaVariants')) {
    function deleteMediaVariants($originalPath, $folder)
    {
        $cacheDir = storage_path("app/public/media/{$folder}/cache");
        $filename = basename($originalPath);
        $nameInfo = pathinfo($filename);

        $pattern = $cacheDir . '/' . $nameInfo['filename'] . '_thumb.*';
        $variants = @glob($pattern);

        if (!$variants) return true;

        $success = true;
        foreach ($variants as $variant) {
            if (file_exists($variant)) {
                $success = $success && @unlink($variant);
            }
        }
        return $success;
    }
}
