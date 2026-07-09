<?php

namespace Plugins\Maps\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GeocodeController extends Controller
{
    public function search(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        if (mb_strlen($q) < 3) {
            return response()->json(['results' => []]);
        }

        $response = Http::withHeaders([
            'User-Agent' => 'LunarBase-Maps/1.0 (' . config('app.url') . ')',
            'Accept-Language' => 'pt-BR,pt;q=0.9,en;q=0.6',
        ])->get('https://nominatim.openstreetmap.org/search', [
            'q'      => $q,
            'format' => 'json',
            'limit'  => 8,
            'addressdetails' => 1,
        ]);

        if (!$response->ok()) {
            return response()->json(['results' => []]);
        }

        $results = collect($response->json())->map(fn ($r) => [
            'lat'          => (float) $r['lat'],
            'lng'          => (float) $r['lon'],
            'display_name' => $r['display_name'] ?? '',
            'type'         => $r['type'] ?? null,
            'class'        => $r['class'] ?? null,
        ])->values();

        return response()->json(['results' => $results]);
    }
}
