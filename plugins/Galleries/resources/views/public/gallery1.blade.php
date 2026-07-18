@php
    // Gera um ID único para esta instância da galeria
    $galleryId = 'gallery-' . \Illuminate\Support\Str::random(6);

    // Mapeia o tamanho para o path da variação
    $sizeMap = [
        'thumb'    => '_thumb',
        'medium'   => '',        // original resized (800px)
        'large'    => '',
        'original' => '',
    ];
@endphp

@once
    <link rel="stylesheet" href="{{ asset('plugins/galleries/css/galleries-public.css') }}">
    @if($lightbox ?? false)
        <script src="{{ asset('plugins/galleries/js/galleries-lightbox.js') }}" defer></script>
    @endif
@endonce

<figure
    id="{{ $galleryId }}"
    class="gallery"
    data-lightbox="{{ $lightbox ? 'true' : 'false' }}"
    style="--gallery-cols: {{ $columns }}; --gallery-gap: {{ $gap }}px;"
>
    <div class="gallery-grid {{ $rounded ? 'gallery-rounded' : '' }}" data-ratio="{{ $ratio }}">
        @foreach ($images as $image)
            @php
                // Escolhe a URL conforme o tamanho
                $url = match ($size) {
                    'thumb'    => $image->thumbnail_url ?? $image->url,
                    'original' => $image->original_url ?? $image->url,
                    default    => $image->url, // medium/large → URL padrão (já redimensionada)
                };
            @endphp

            <div class="gallery-item">
                @if ($lightbox)
                    <button
                        type="button"
                        class="gallery-link"
                        data-full="{{ $image->original_url ?? $image->url }}"
                        data-caption="{{ e($image->caption ?? $image->alt ?? '') }}"
                        aria-label="Ampliar imagem{{ $image->alt ? ': ' . e($image->alt) : '' }}"
                    >
                        <img
                            src="{{ $url }}"
                            alt="{{ $image->alt ?? '' }}"
                            loading="lazy"
                            class="gallery-image"
                        >
                        @if ($caption && ($image->caption || $image->alt))
                            <span class="gallery-caption">{{ $image->caption ?? $image->alt }}</span>
                        @endif
                        <span class="gallery-zoom-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="7"/>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                                <line x1="11" y1="8" x2="11" y2="14"/>
                                <line x1="8" y1="11" x2="14" y2="11"/>
                            </svg>
                        </span>
                    </button>
                @else
                    <div class="gallery-link">
                        <img
                            src="{{ $url }}"
                            alt="{{ $image->alt ?? '' }}"
                            loading="lazy"
                            class="gallery-image"
                        >
                        @if ($caption && ($image->caption || $image->alt))
                            <span class="gallery-caption">{{ $image->caption ?? $image->alt }}</span>
                        @endif
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</figure>
