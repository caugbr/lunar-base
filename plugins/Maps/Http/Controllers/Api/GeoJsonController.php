<?php

namespace Plugins\Maps\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Plugins\Maps\Support\NominatimClient;

/**
 * Gerencia GeoJSON pré-cadastrados armazenados em plugins/Maps/resources/geojson/.
 *
 * Estrutura:
 *   resources/geojson/index.json    — [{ "pid": "brasil", "name": "Brasil", "type": "country" }, ...]
 *   resources/geojson/{pid}.json    — FeatureCollection
 */
class GeoJsonController extends Controller
{
    protected string $dir;
    protected string $indexPath;

    public function __construct()
    {
        $this->dir = base_path('plugins/Maps/resources/geojson');
        $this->indexPath = $this->dir . '/index.json';

        if (!is_dir($this->dir)) {
            @mkdir($this->dir, 0755, true);
        }
        if (!file_exists($this->indexPath)) {
            file_put_contents($this->indexPath, "[]\n");
        }
    }

    /** GET /api/maps/geojson — lista o índice */
    public function index()
    {
        return response()->json($this->readIndex());
    }

    /** GET /api/maps/geojson/{pid} — retorna FeatureCollection */
    public function show(string $pid)
    {
        $pid = $this->safeName($pid);
        $file = $this->dir . '/' . $pid . '.json';
        if (!is_file($file)) {
            return response()->json(['error' => 'Not found'], 404);
        }
        $content = json_decode(file_get_contents($file), true);
        if ($content === null) {
            return response()->json(['error' => 'Invalid JSON'], 500);
        }
        return response()->json($content);
    }

    /**
     * POST /api/maps/geojson/find
     * Body: { country, state, city, neighborhood } — busca no Nominatim e devolve preview
     */
    public function find(Request $request, NominatimClient $nominatim)
    {
        $query = collect($request->only(['country', 'state', 'city', 'neighborhood']))
            ->filter()->all();

        if (empty($query)) {
            return response()->json(['results' => []]);
        }

        $results = $nominatim->searchBoundary($query);
        return response()->json(['results' => $results]);
    }

    /**
     * POST /api/maps/geojson/save
     * Body: { osm_id, osm_type, name, pid }  →  baixa o polygon e salva localmente.
     */
    public function save(Request $request, NominatimClient $nominatim)
    {
        $validated = $request->validate([
            'osm_id'   => 'required|integer',
            'osm_type' => 'required|in:relation,way,node',
            'name'     => 'required|string|max:200',
            'pid'      => 'required|string|max:64|regex:/^[a-z0-9_-]+$/i',
        ]);

        $pid = $this->safeName($validated['pid']);
        $file = $this->dir . '/' . $pid . '.json';

        if (file_exists($file)) {
            return response()->json(['error' => 'Já existe um lugar com esse identificador'], 422);
        }

        $polygon = $nominatim->fetchPolygon($validated['osm_type'], $validated['osm_id']);
        if (!$polygon) {
            return response()->json(['error' => 'Não foi possível obter o polygon'], 502);
        }

        file_put_contents($file, json_encode($polygon, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        $index = $this->readIndex();
        $index[] = [
            'pid'  => $pid,
            'name' => $validated['name'],
            'type' => $polygon['features'][0]['properties']['type'] ?? 'boundary',
        ];
        file_put_contents($this->indexPath, json_encode($index, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        return response()->json(['ok' => true, 'pid' => $pid]);
    }

    protected function readIndex(): array
    {
        $data = json_decode(file_get_contents($this->indexPath), true);
        return is_array($data) ? $data : [];
    }

    protected function safeName(string $s): string
    {
        return preg_replace('/[^a-z0-9_-]+/i', '', $s);
    }
}
