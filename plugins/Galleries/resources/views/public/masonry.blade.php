<div class="gallery-masonry" style="column-count: {{ $columns }}; column-gap: {{ $gap }}px;">
    @foreach ($images as $image)
        @php
            $url = match ($size) {
                'thumb' => $image->thumbnail_url ?? $image->url,
                'original' => $image->original_url ?? $image->url,
                default => $image->url,
            };
        @endphp

        <div class="gallery-masonry-item {{ $rounded ? 'gallery-rounded' : '' }}" style="break-inside: avoid; margin-bottom: {{ $gap }}px;">
            @if ($lightbox)
                <button type="button" class="gallery-link" data-full="{{ $image->original_url ?? $image->url }}" data-caption="{{ e($image->caption ?? $image->alt ?? '') }}">
                    <img src="{{ $url }}" alt="{{ $image->alt ?? '' }}" loading="lazy" class="gallery-image" style="width: 100%; height: auto; display: block;">
                    @if ($caption && ($image->caption || $image->alt))
                        <span class="gallery-caption">{{ $image->caption ?? $image->alt }}</span>
                    @endif
                </button>
            @else
                <div class="gallery-link">
                    <img src="{{ $url }}" alt="{{ $image->alt ?? '' }}" loading="lazy" class="gallery-image" style="width: 100%; height: auto; display: block;">
                </div>
            @endif
        </div>
    @endforeach
</div>
