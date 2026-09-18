<?php

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

/**
 * Gera as variações responsivas de tamanho baseando-se no Ponto Focal (Focal Point)
 */
if (!function_exists('generateMediaVariants')) {
    function generateMediaVariants($originalPath, $folder, $settings = [])
    {
        if (str_ends_with(strtolower($originalPath), '.svg')) {
            return false;
        }

        $quality  = max(1, min(100, (int) ($settings['media_quality'] ?? 80)));
        $format   = strtolower($settings['media_formats'] ?? 'webp');

        // Extrai o ponto focal em porcentagem (0 a 100). Padrão: 50% (centro)
        $focalX = max(0, min(100, (float) ($settings['focal_x'] ?? $settings['focalX'] ?? 50)));
        $focalY = max(0, min(100, (float) ($settings['focal_y'] ?? $settings['focalY'] ?? 50)));

        $fullPath = storage_path('app/public/' . $originalPath);
        if (!file_exists($fullPath)) return false;

        $pathInfo = pathinfo($originalPath);
        $ext = in_array($format, ['jpeg', 'jpg', 'png', 'webp']) ? $format : $pathInfo['extension'];

        $cacheDir = "media/{$folder}/cache";
        $variantFullPathDir = storage_path('app/public/' . $cacheDir);

        if (!file_exists($theme_test_container = $variantFullPathDir)) {
            mkdir($theme_test_container, 0755, true);
        }

        // Breakpoints Padrão do Sistema
        $sizes = [
            'thumb' => [
                'width'  => (int) ($settings['media_thumbnail_width'] ?? 300),
                'height' => (int) ($settings['media_thumbnail_height'] ?? 300),
                'crop'   => (bool) ($settings['media_crop_thumbnail'] ?? true),
            ],
            'large' => [
                'width'  => 1200,
                'height' => 630, // Formato OpenGraph (1.91:1)
                'crop'   => true,
            ]
        ];

        $extraSizes = config('imageSizes', []);
        $sizes = array_merge($sizes, $extraSizes);

        try {
            $manager = new ImageManager(new Driver());

            foreach ($sizes as $name => $config) {
                $image = $manager->read($fullPath);

                $targetW = !empty($config['width']) ? (int) $config['width'] : null;
                $targetH = !empty($config['height']) ? (int) $config['height'] : null;
                $mustCrop = (bool) ($config['crop'] ?? false);

                if ($mustCrop && $targetW && $targetH) {
                    $origW = $image->width();
                    $origH = $image->height();

                    $targetRatio = $targetW / $targetH;
                    $origRatio   = $origW / $origH;

                    if ($origRatio > $targetRatio) {
                        // Imagem original é mais larga: cortamos nas laterais (eixo X)
                        $cropH = $origH;
                        $cropW = $origH * $targetRatio;

                        // Localiza o pixel real do ponto focal no eixo X
                        $focusPixelX = $origW * ($focalX / 100);

                        // Centraliza a janela de corte no ponto focal
                        $offsetX = $focusPixelX - ($cropW / 2);

                        // Trava matemática: impede que a janela ultrapasse os limites
                        $offsetX = max(0, min($origW - $cropW, $offsetX));
                        $offsetY = 0;
                    } else {
                        // Imagem original é mais alta: cortamos topo/base (eixo Y)
                        $cropW = $origW;
                        $cropH = $origW / $targetRatio;

                        // Localiza o pixel real do ponto focal no eixo Y
                        $focusPixelY = $origH * ($focalY / 100);

                        // Centraliza a janela de corte no ponto focal
                        $offsetY = $focusPixelY - ($cropH / 2);

                        // Trava matemática: não deixar sair da borda superior nem inferior
                        $offsetY = max(0, min($origH - $cropH, $offsetY));
                        $offsetX = 0;
                    }

                    // 1. Recorta a janela calculada ao redor do ponto de foco
                    $image->crop(
                        (int) round($cropW),
                        (int) round($cropH),
                        (int) round($offsetX),
                        (int) round($offsetY)
                    );

                    // 2. Redimensiona para o tamanho final pretendido
                    $image->resize($targetW, $targetH);

                } else {
                    // Sem corte forçado: preserva a proporção original
                    $image->scale(width: $targetW, height: $targetH);
                }

                $encoded = match($ext) {
                    'png'   => $image->toPng(),
                    'webp'  => $image->toWebp($quality),
                    default => $image->toJpeg($quality),
                };

                $variantName = $pathInfo['filename'] . '_' . $name . '.' . $ext;
                $variantFullPath = "{$variantFullPathDir}/{$variantName}";

                $encoded->save($variantFullPath);
            }

            return true;

        } catch (\Exception $e) {
            \Log::error("Erro ao gerar variações responsivas: " . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('deleteMediaVariants')) {
    function deleteMediaVariants($originalPath, $folder)
    {
        if (str_ends_with(strtolower($originalPath), '.svg')) return true;

        $cacheDir = storage_path("app/public/media/{$folder}/cache");
        $filename = basename($originalPath);
        $nameInfo = pathinfo($filename);

        $pattern = $cacheDir . '/' . $nameInfo['filename'] . '_*.*';
        $variants = @glob($pattern);

        if (!$variants) return true;

        foreach ($variants as $variant) {
            if (file_exists($variant)) @unlink($variant);
        }
        return true;
    }
}
