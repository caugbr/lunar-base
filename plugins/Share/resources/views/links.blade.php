{{-- Injeção isolada da folha de estilos externa do tema --}}
@once
@push('styles')
<link rel="stylesheet" href="{{ asset('plugins/share/css/share.css') }}">
@endpush
@endonce

<div class="reaction-and-share-row">
    <!-- Bloco Opcional: Se quiser, as reações do post podem ser renderizadas no mesmo alinhamento -->
    <div class="share-wrapper">
        <span class="share-label">Compartilhar</span>
        <div class="share-buttons-list">
            @foreach($activeNetworks as $key => $config)
                <a href="{{ $config['share_url'] }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="share-item share-{{ $key }}"
                   title="Compartilhar no {{ $config['label'] }}"
                   aria-label="Compartilhar no {{ $config['label'] }}">
                    @if($config['svg_img'])
                    <img src="{{ $config['svg_img'] }}">
                    @else
                    @include('share::icons', ['network' => $key])
                    @endif
                </a>
            @endforeach
        </div>
    </div>
</div>
