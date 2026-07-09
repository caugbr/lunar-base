<?php

namespace Plugins\Maps\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Plugins\Maps\Models\Map;
use Plugins\Maps\Models\MapMarker;

class MapController extends Controller
{
    public function index()
    {
        $maps = Map::withCount('markers')->orderByDesc('id')->paginate(20);
        return view('maps::admin.index', compact('maps'));
    }

    public function create()
    {
        $map = new Map([
            'center_lat'         => (float) setting('maps_default_lat', -23.5505),
            'center_lng'         => (float) setting('maps_default_lng', -46.6333),
            'zoom'               => (int)   setting('maps_default_zoom', 13),
            'width'              => 800,
            'height'             => 500,
            'fullwidth'          => true,
            'show_zoom_controls' => true,
            'allow_drag'         => true,
            'allow_scroll_zoom'  => false,
        ]);
        return view('maps::admin.edit', compact('map'));
    }

    public function edit(Map $map)
    {
        $map->load('markers');
        return view('maps::admin.edit', compact('map'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $map = Map::create($data['map']);
        $this->syncMarkers($map, $data['markers']);
        $this->handleJsonImport($map, $request);

        return redirect()->route('admin.maps.edit', $map->id)
            ->with('success', 'Mapa criado com sucesso.');
    }

    public function update(Request $request, Map $map)
    {
        $data = $this->validateData($request, $map);
        $map->update($data['map']);
        $this->syncMarkers($map, $data['markers']);
        $this->handleJsonImport($map, $request);

        return redirect()->route('admin.maps.edit', $map->id)
            ->with('success', 'Mapa atualizado com sucesso.');
    }

    public function destroy(Map $map)
    {
        $map->markers()->delete();
        $map->delete();
        return redirect()->route('admin.maps.index')->with('success', 'Mapa removido.');
    }

    // ─────────────────────────────────────────────────────────────

    protected function validateData(Request $request, ?Map $map = null): array
    {
        $validated = $request->validate([
            'title'              => 'required|string|max:255',
            'description'        => 'nullable|string|max:1000',
            'center_lat'         => 'required|numeric|between:-90,90',
            'center_lng'         => 'required|numeric|between:-180,180',
            'zoom'               => 'required|integer|min:1|max:19',
            'width'              => 'nullable|integer|min:100|max:2400',
            'height'             => 'required|integer|min:100|max:1600',
            'fullwidth'          => 'nullable|boolean',
            'show_zoom_controls' => 'nullable|boolean',
            'allow_drag'         => 'nullable|boolean',
            'allow_scroll_zoom'  => 'nullable|boolean',

            'geojson_place'       => 'nullable|string|max:128',
            'geojson_inline_raw'  => 'nullable|string',
            'geojson_color'       => 'nullable|string|max:16',
            'geojson_weight'      => 'nullable|integer|min:0|max:10',
            'geojson_opacity'     => 'nullable|numeric|min:0|max:1',
            'geojson_fill_color'  => 'nullable|string|max:16',
            'geojson_fill_opacity'=> 'nullable|numeric|min:0|max:1',

            'markers'            => 'nullable|array',
            'markers.*.id'       => 'nullable|integer',
            'markers.*.uid'      => 'nullable|string|max:32',
            'markers.*.title'    => 'required_with:markers|string|max:255',
            'markers.*.content'  => 'nullable|string|max:5000',
            'markers.*.lat'      => 'required_with:markers|numeric|between:-90,90',
            'markers.*.lng'      => 'required_with:markers|numeric|between:-180,180',
            'markers.*.color'    => 'nullable|string|max:16',
            'markers.*.icon'     => 'nullable|string|max:64',
            'markers.*.parameters' => 'nullable|string|max:2000',
        ]);

        // Parse do GeoJSON inline (textarea) — só aceita JSON válido
        $inline = null;
        if (!empty($validated['geojson_inline_raw'])) {
            $decoded = json_decode($validated['geojson_inline_raw'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $inline = $decoded;
            }
        }

        $mapData = [
            'title'              => $validated['title'],
            'slug'               => $map?->slug ?? Str::slug($validated['title']) . '-' . Str::random(4),
            'description'        => $validated['description'] ?? null,
            'center_lat'         => $validated['center_lat'],
            'center_lng'         => $validated['center_lng'],
            'zoom'               => $validated['zoom'],
            'width'              => $validated['width']  ?? 800,
            'height'             => $validated['height'],
            'fullwidth'          => (bool) ($request->input('fullwidth', false)),
            'show_zoom_controls' => (bool) ($request->input('show_zoom_controls', false)),
            'allow_drag'         => (bool) ($request->input('allow_drag', false)),
            'allow_scroll_zoom'  => (bool) ($request->input('allow_scroll_zoom', false)),

            'geojson_place'        => $validated['geojson_place'] ?? null,
            'geojson_inline'       => $inline,
            'geojson_color'        => $validated['geojson_color'] ?? '#ff7800',
            'geojson_weight'       => $validated['geojson_weight'] ?? 3,
            'geojson_opacity'      => $validated['geojson_opacity'] ?? 0.8,
            'geojson_fill_color'   => $validated['geojson_fill_color'] ?? '#ffa500',
            'geojson_fill_opacity' => $validated['geojson_fill_opacity'] ?? 0.2,
        ];

        return [
            'map'     => $mapData,
            'markers' => $validated['markers'] ?? [],
        ];
    }

    protected function syncMarkers(Map $map, array $markers): void
    {
        $keepIds = [];
        foreach ($markers as $m) {
            $payload = [
                'uid'        => $m['uid'] ?? MapMarker::generateUid(),
                'title'      => trim($m['title'] ?? ''),
                'content'    => $m['content'] ?? null,
                'lat'        => (float) $m['lat'],
                'lng'        => (float) $m['lng'],
                'color'      => $m['color'] ?? '#e74c3c',
                'icon'       => $m['icon'] ?? 'map-pin',
                'parameters' => $this->parseParameters($m['parameters'] ?? null),
            ];

            if (!empty($m['id'])) {
                $existing = $map->markers()->find($m['id']);
                if ($existing) {
                    $existing->update($payload);
                    $keepIds[] = $existing->id;
                    continue;
                }
            }
            $created = $map->markers()->create($payload);
            $keepIds[] = $created->id;
        }

        // Remove markers que não estão mais na lista
        $map->markers()->whereNotIn('id', $keepIds ?: [0])->delete();
    }

    /**
     * Aceita parameters no formato URL-encoded (a=1&b=2) OU JSON — sempre retorna array.
     */
    protected function parseParameters($raw): ?array
    {
        if (empty($raw)) return null;
        if (is_array($raw)) return $raw;

        $decoded = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }
        parse_str((string) $raw, $parsed);
        return $parsed ?: null;
    }

    /**
     * Importa markers de um upload JSON — mesmo formato do plugin WP (llegado.json).
     */
    protected function handleJsonImport(Map $map, Request $request): void
    {
        if (!$request->hasFile('markers_json')) return;

        $file = $request->file('markers_json');
        if (!$file->isValid()) return;

        $content = @file_get_contents($file->getRealPath());
        $data = json_decode($content, true);
        if (!is_array($data)) return;

        $existingUids = $map->markers()->pluck('uid')->filter()->all();

        foreach ($data as $row) {
            if (empty($row['lat']) || empty($row['lng'])) continue;
            if (!empty($row['uid']) && in_array($row['uid'], $existingUids, true)) continue;

            $map->markers()->create([
                'uid'        => $row['uid'] ?? MapMarker::generateUid(),
                'title'      => trim($row['title'] ?? ''),
                'content'    => $row['content'] ?? null,
                'lat'        => (float) $row['lat'],
                'lng'        => (float) $row['lng'],
                'color'      => $row['color'] ?? '#e74c3c',
                'icon'       => $row['icon'] ?? 'map-pin',
                'parameters' => $this->parseParameters($row['parameters'] ?? null),
            ]);
        }
    }
}
