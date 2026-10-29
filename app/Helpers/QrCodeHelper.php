<?php

use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

if (! function_exists('qrCodeSvg')) {
    /**
     * Gera uma string SVG pura contendo o QR Code.
     */
    function qrCodeSvg(string $content, int $size = 200, int $margin = 1): string
    {
        if (empty(trim($content))) {
            return '';
        }

        try {
            $renderer = new ImageRenderer(
                new RendererStyle($size, $margin),
                new SvgImageBackEnd()
            );

            $writer = new Writer($renderer);

            return $writer->writeString($content);
        } catch (\Throwable $e) {
            return '<!-- Erro ao gerar QR Code: ' . e($e->getMessage()) . ' -->';
        }
    }
}

if (! function_exists('saveQrCodeSvg')) {
    /**
     * Gera um QR Code em SVG e salva no disco do Storage.
     *
     * @param string $content   Conteúdo ou URL do QR Code
     * @param string $path      Diretório de destino (padrão: 'qrcodes') ou caminho com .svg
     * @param string $disk      Disco do storage (padrão: 'public')
     * @param int    $size      Tamanho em pixels
     * @param int    $margin    Margem interna
     * @return string|null      Caminho relativo salvo (ex: 'qrcodes/NBGvWjqN...svg') ou null se falhar
     */
    function saveQrCodeSvg(string $content, string $path = 'qrcodes', string $disk = 'public', int $size = 200, int $margin = 1): ?string
    {
        if (empty(trim($content))) {
            return null;
        }

        $svg = qrCodeSvg($content, $size, $margin);

        // Se falhou ao gerar
        if (empty($svg) || str_starts_with($svg, '<!--')) {
            return null;
        }

        // Se foi passado um caminho com nome de arquivo .svg, usa direto
        if (str_ends_with(strtolower($path), '.svg')) {
            $filePath = $path;
        } else {
            // Caso contrário, gera um hash aleatório idêntico ao padrão do Laravel
            $filename = Str::random(40) . '.svg';
            $filePath = trim($path, '/') . '/' . $filename;
        }

        Storage::disk($disk)->put($filePath, $svg);

        return $filePath;
    }
}
