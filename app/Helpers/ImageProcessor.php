<?php

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

/**
 * Gera as variações responsivas de tamanho para o tema e SEO (WordPress style)
 */
if (!function_exists('generateMediaVariants')) {
    function generateMediaVariants($originalPath, $folder, $settings = [])
    {
        if (str_ends_with(strtolower($originalPath), '.svg')) {
            return false;
        }

        $quality  = max(1, min(100, (int) ($settings['media_quality'] ?? 80)));
        $format   = strtolower($settings['media_formats'] ?? 'webp');

        $fullPath = storage_path('app/public/' . $originalPath);
        if (!file_exists($fullPath)) return false;

        $pathInfo = pathinfo($originalPath);
        $ext = in_array($format, ['jpeg', 'jpg', 'png', 'webp']) ? $format : $pathInfo['extension'];

        $cacheDir = "media/{$folder}/cache";
        $variantFullPathDir = storage_path('app/public/' . $cacheDir);

        if (!file_exists($theme_test_container = $variantFullPathDir)) {
            mkdir($theme_test_container, 0755, true);
        }

        // Definição dos Breakpoints Padrão do Sistema
        $sizes = [
            'thumb' => [
                'width' => (int) ($settings['media_thumbnail_width'] ?? 300),
                'height' => (int) ($settings['media_thumbnail_height'] ?? 300),
                'crop' => (bool) ($settings['media_crop_thumbnail'] ?? true),
                'position' => $settings['media_crop_position'] ?? 'center',
            ],
            'large' => [
                'width' => 1200,
                'height' => 630, // Proporção exata recomendada para redes sociais (1.91:1)
                'crop' => true,
                'position' => 'center',
            ]
        ];

        $extraSizes = config('imageSizes', []);
        $sizes = array_merge($sizes, $extraSizes);

        try {
            $manager = new ImageManager(new Driver());

            foreach ($sizes as $name => $config) {
                // Abre uma nova leitura da imagem para cada variação
                $image = $manager->read($fullPath);

                if ($config['crop']) {
                    $image->cover($config['width'], $config['height'], $config['position'] ?? 'center');
                } else {
                    $image->scale(width: $config['width'], height: $config['height']);
                }

                $encoded = match($ext) {
                    'png'  => $image->toPng(),
                    'webp' => $image->toWebp($quality),
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

/**
 * Deleta fisicamente todas as variações em cache ao remover a imagem
 */
if (!function_exists('deleteMediaVariants')) {
    function deleteMediaVariants($originalPath, $folder)
    {
        if (str_ends_with(strtolower($originalPath), '.svg')) return true;

        $cacheDir = storage_path("app/public/media/{$folder}/cache");
        $filename = basename($originalPath);
        $nameInfo = pathinfo($filename);

        // Encontra qualquer variação com o padrão do arquivo (ex: _thumb, _medium, _large)
        $pattern = $cacheDir . '/' . $nameInfo['filename'] . '_*.*';
        $variants = @glob($pattern);

        if (!$variants) return true;

        foreach ($variants as $variant) {
            if (file_exists($variant)) @unlink($variant);
        }
        return true;
    }
}
