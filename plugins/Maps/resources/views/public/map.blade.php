@php
    $mapId = 'lunar-map-' . $map->id;
@endphp

@once
@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="{{ asset('plugins/maps/css/maps.css') }}">
@endpush
@endonce

<div id="{{ $mapId }}" class="lunar-map-container" style="height: {{ $map->height }}px;"></div>

@if($map->description)
    <p class="map-description" style="font-size: 0.875rem; color: var(--color-text-muted); margin-top: 0.5rem;">
        {{ $map->description }}
    </p>
@endif

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="{{ asset('plugins/maps/js/maps.js') }}"></script>
<script>
    window.lunarMaps = window.lunarMaps || {};
    window.lunarMaps['{{ $mapId }}'] = @json([
        'center_lat' => $map->center_lat,
        'center_lng' => $map->center_lng,
        'zoom' => $map->zoom,
        'show_zoom_controls' => $map->show_zoom_controls,
        'allow_drag' => $map->allow_drag,
        'allow_scroll_zoom' => $map->allow_scroll_zoom,
        'tile_url' => setting('maps_tile_url', 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png'),
        'attribution' => setting('maps_attribution', '&copy; OpenStreetMap'),
        'markers' => $map->markers->map(fn($m) => [
            'lat' => $m->lat,
            'lng' => $m->lng,
            'title' => $m->title,
            'content' => $m->content,
            'color' => $m->color,
        ])->toArray(),
    ]);
</script>
@endpush
