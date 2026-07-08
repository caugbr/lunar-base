<?php

namespace Plugins\Maps\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Plugins\Maps\Models\Map;
use Plugins\Maps\Models\MapMarker;

class MapController extends Controller
{
    public function index()
    {
        $maps = Map::withCount('markers')->latest()->paginate(setting('reading.pagination_max_items', 15));
        return view('maps::admin.index', compact('maps'));
    }

    public function create()
    {
        $map = new Map([
            'center_lat' => setting('maps_default_lat', '-23.5505'),
            'center_lng' => setting('maps_default_lng', '-46.6333'),
            'zoom' => setting('maps_default_zoom', 13),
            'height' => 400,
            'show_zoom_controls' => true,
            'allow_drag' => true,
            'allow_scroll_zoom' => true,
        ]);

        return view('maps::admin.edit', compact('map'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateMap($request);
        $map = Map::create($validated);
        $this->syncMarkers($map, $request->input('markers', []));

        log_admin("Mapa criado: {$map->title}", "maps");

        return redirect()->route('admin.maps.edit', $map->id)
            ->with('success', 'Mapa criado com sucesso!');
    }

    public function edit(Map $map)
    {
        $map->load('markers');
        return view('maps::admin.edit', compact('map'));
    }

    public function update(Request $request, Map $map)
    {
        $validated = $this->validateMap($request, $map->id);
        $map->update($validated);
        $this->syncMarkers($map, $request->input('markers', []));

        log_admin("Mapa atualizado: {$map->title}", "maps");

        return redirect()->route('admin.maps.edit', $map->id)
            ->with('success', 'Mapa atualizado com sucesso!');
    }

    public function destroy(Map $map)
    {
        log_admin("Mapa excluído: {$map->title}", "maps");
        $map->delete();

        return redirect()->route('admin.maps.index')
            ->with('success', 'Mapa excluído.');
    }

    private function validateMap(Request $request, ?int $excludeId = null): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:maps,slug,' . $excludeId,
            'center_lat' => 'required|numeric|between:-90,90',
            'center_lng' => 'required|numeric|between:-180,180',
            'zoom' => 'required|integer|between:1,18',
            'height' => 'required|integer|between:100,1200',
            'show_zoom_controls' => 'boolean',
            'allow_drag' => 'boolean',
            'allow_scroll_zoom' => 'boolean',
            'description' => 'nullable|string',
        ]);
    }

    private function syncMarkers(Map $map, array $markers): void
    {
        $keepIds = [];

        foreach ($markers as $index => $markerData) {
            if (empty($markerData['title']) && empty($markerData['lat'])) {
                continue;
            }

            $marker = $map->markers()->updateOrCreate(
                ['id' => $markerData['id'] ?? null],
                [
                    'title' => $markerData['title'] ?? 'Marcador',
                    'lat' => (float) ($markerData['lat'] ?? 0),
                    'lng' => (float) ($markerData['lng'] ?? 0),
                    'content' => $markerData['content'] ?? null,
                    'color' => $markerData['color'] ?? '#e74c3c',
                    'icon' => $markerData['icon'] ?? 'map-pin',
                    'sort_order' => $index,
                ]
            );

            $keepIds[] = $marker->id;
        }

        $map->markers()->whereNotIn('id', $keepIds)->delete();
    }
}
