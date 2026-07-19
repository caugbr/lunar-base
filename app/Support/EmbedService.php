<?php

namespace App\Support;

use Embed\Embed;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EmbedService
{
    /**
     * Resolve uma URL externa e retorna o bloco de marcação HTML correspondente.
     */
    public static function resolve(string $url): string
    {
        // 💡 CORREÇÃO: Remove tags HTML (como links <a> injetados por editores visuais)
        $url = trim(strip_tags($url));

        if (empty($url)) {
            return '';
        }

        // 1. EXCEÇÃO MANUAL: GitHub Gist
        if (str_contains($url, 'gist.github.com')) {
            $urlClean = preg_replace('/\.js$/', '', $url);
            return '<script src="' . e($urlClean) . '.js"></script>';
        }

        // 2. EXCEÇÃO MANUAL: Google Maps
        if (str_contains($url, 'google.com/maps') || str_contains($url, 'maps.app.goo.gl')) {
            return '<div class="embed-wrapper embed-maps" style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;margin:1.5rem 0;">' .
                   '<iframe src="' . e($url) . '" style="position:absolute;top:0;left:0;width:100%;height:100%;border:0;" allowfullscreen="" loading="lazy"></iframe>' .
                   '</div>';
        }

        // 3. MOTOR PRINCIPAL: oEmbed através da biblioteca "embed/embed" (v4) com Caching
        try {
            $cacheKey = 'embed_' . md5($url);

            $embedData = Cache::remember($cacheKey, now()->addDays(30), function () use ($url) {
                $embed = new Embed();
                $info = $embed->get($url);

                return [
                    'code' => (string) $info->code,
                    'provider' => Str::slug($info->providerName ?? 'generic')
                ];
            });

            if ($embedData && !empty($embedData['code'])) {

                $isAudio = in_array($embedData['provider'], ['spotify', 'soundcloud']);

                if ($isAudio) {
                    return '<div class="embed-wrapper embed-' . e($embedData['provider']) . '" style="margin:1.5rem 0;">' .
                           $embedData['code'] .
                           '</div>';
                }

                // 💡 CORREÇÃO: Adicionada a classe auxiliar 'embed-responsive' para os vídeos
                return '<div class="embed-wrapper embed-responsive embed-' . e($embedData['provider']) . '" style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;margin:1.5rem 0;">' .
                       $embedData['code'] .
                       '</div>';
            }

        } catch (\Exception $e) {
            Log::warning("Erro ao tentar processar oEmbed via EmbedService para a URL [{$url}]: " . $e->getMessage());
        }

        return '<p class="embed-fallback"><a href="' . e($url) . '" target="_blank" rel="noopener noreferrer" class="embed-link">' . e($url) . '</a></p>';
    }
}
