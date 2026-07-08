<?php

namespace Plugins\Maps\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GeocodeController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $request->validate(['q' => 'required|string|min:3|max:255']);

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'LunarBase-Maps/1.0',
            ])->get('https://nominatim.openstreetmap.org/search', [
                'q' => $request->input('q'),
                'format' => 'json',
                'limit' => 5,
                'addressdetails' => 1,
            ]);

            if ($response->successful()) {
                $results = collect($response->json())->map(fn ($item) => [
                    'lat' => (float) $item['lat'],
                    'lng' => (float) $item['lon'],
                    'display_name' => $item['display_name'],
                ]);

                return response()->json(['results' => $results]);
            }

            return response()->json(['results' => [], 'error' => 'Falha na busca'], 422);
        } catch (\Exception $e) {
            return response()->json(['results' => [], 'error' => $e->getMessage()], 500);
        }
    }
}
