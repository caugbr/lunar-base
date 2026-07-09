{{-- Renderiza a lista de marcadores de um mapa como cards clicáveis --}}
@once
@push('styles')
    <link rel="stylesheet" href="{{ asset('plugins/maps/css/maps.css') }}">
@endpush
@endonce

<ul class="lunar-map-list" data-map-id="{{ $map->id }}">
    @foreach($map->markers as $marker)
        <li class="item" style="--pin-color: {{ $marker->color }};"
            data-lat="{{ $marker->lat }}" data-lng="{{ $marker->lng }}">
            <span class="dot"></span>
            <div>
                <strong>{{ $marker->title }}</strong>
                @if($marker->content)
                    <small>{{ Str::limit(strip_tags($marker->content), 100) }}</small>
                @endif
            </div>
        </li>
    @endforeach
</ul>
