<?php

namespace Plugins\Maps\Support;

use Illuminate\Support\Facades\Http;

/**
 * Wrapper minimalista do Nominatim (OSM) para busca de boundaries administrativos.
 */
class NominatimClient
{
    protected string $ua;

    public function __construct()
    {
        $this->ua = 'LunarBase-Maps/1.0 (' . config('app.url') . ')';
    }

    /**
     * Busca boundary por país/estado/cidade/bairro.
     * Retorna resultados enxutos: [{ osm_id, osm_type, name, display_name, class, type }].
     */
    public function searchBoundary(array $parts): array
    {
        $params = array_merge([
            'format'         => 'jsonv2',
            'addressdetails' => 1,
            'polygon_geojson'=> 0,
            'limit'          => 5,
        ], array_filter($parts));

        $response = Http::withHeaders([
            'User-Agent'     => $this->ua,
            'Accept-Language'=> 'pt-BR,pt;q=0.9,en;q=0.6',
        ])->get('https://nominatim.openstreetmap.org/search', $params);

        if (!$response->ok()) return [];

        return collect($response->json())
            ->filter(fn ($r) => in_array($r['class'] ?? '', ['boundary', 'place']))
            ->map(fn ($r) => [
                'osm_id'       => $r['osm_id'] ?? null,
                'osm_type'     => $r['osm_type'] ?? null,
                'name'         => $r['name'] ?? ($r['display_name'] ?? ''),
                'display_name' => $r['display_name'] ?? '',
                'class'        => $r['class'] ?? null,
                'type'         => $r['type'] ?? null,
            ])
            ->values()->all();
    }

    /**
     * Baixa o polygon GeoJSON completo para um osm_id/osm_type.
     * Retorna uma FeatureCollection ou null.
     */
    public function fetchPolygon(string $osmType, int $osmId): ?array
    {
        $typeLetter = match ($osmType) {
            'relation' => 'R',
            'way'      => 'W',
            'node'     => 'N',
            default    => null,
        };
        if (!$typeLetter) return null;

        $response = Http::withHeaders(['User-Agent' => $this->ua])
            ->get('https://nominatim.openstreetmap.org/lookup', [
                'osm_ids'         => $typeLetter . $osmId,
                'format'          => 'json',
                'polygon_geojson' => 1,
            ]);

        if (!$response->ok()) return null;
        $data = $response->json();
        if (empty($data[0]['geojson'])) return null;

        $item = $data[0];
        return [
            'type'     => 'FeatureCollection',
            'features' => [[
                'type' => 'Feature',
                'geometry' => $item['geojson'],
                'properties' => [
                    'name'         => $item['display_name'] ?? '',
                    'osm_id'       => $item['osm_id'] ?? null,
                    'osm_type'     => $item['osm_type'] ?? null,
                    'type'         => $item['type'] ?? 'boundary',
                ],
            ]],
        ];
    }
}
