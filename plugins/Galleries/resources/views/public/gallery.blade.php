@php
    // Gera um ID único para esta instância da galeria
    $galleryId = 'gallery-' . \Illuminate\Support\Str::random(6);
@endphp

@once
    <link rel="stylesheet" href="{{ asset('plugins/galleries/css/galleries-public.css') }}">
    @if($lightbox ?? false)
        <script src="{{ asset('plugins/galleries/js/galleries-lightbox.js') }}" defer></script>
    @endif
    @if(($layout ?? 'grid') === 'carousel')
        <script src="{{ asset('plugins/galleries/js/galleries-carousel.js') }}" defer></script>
    @endif
@endonce

<figure
    id="{{ $galleryId }}"
    class="gallery gallery--{{ $layout }}"
    data-lightbox="{{ $lightbox ? 'true' : 'false' }}"
    style="--gallery-cols: {{ $columns }}; --gallery-gap: {{ $gap }}px;"
>
    {{-- 👇 A MÁGICA: Inclui dinamicamente o arquivo do layout escolhido --}}
    @include("galleries::public.{$layout}")
</figure>
