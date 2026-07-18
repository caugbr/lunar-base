<div class="gallery-carousel" style="--gallery-gap: {{ $gap }}px;">
    <div class="gallery-carousel-track">
        @foreach ($images as $image)
            @php
                $url = $size === 'thumb' ? ($image->thumbnail_url ?? $image->url) : $image->url;
            @endphp
            <div class="gallery-carousel-item {{ $rounded ? 'gallery-rounded' : '' }}">
                @if ($lightbox)
                    <button type="button" class="gallery-link" data-full="{{ $image->original_url ?? $image->url }}" data-caption="{{ e($image->caption ?? $image->alt ?? '') }}">
                        <img src="{{ $url }}" alt="{{ $image->alt ?? '' }}" loading="lazy">
                    </button>
                @else
                    <img src="{{ $url }}" alt="{{ $image->alt ?? '' }}" loading="lazy">
                @endif
            </div>
        @endforeach
    </div>
    {{-- Botões de navegação (requer JS para funcionar) --}}
    <button class="carousel-btn prev" aria-label="Anterior">‹</button>
    <button class="carousel-btn next" aria-label="Próxima">›</button>
</div>
