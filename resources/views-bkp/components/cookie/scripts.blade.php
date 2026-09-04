<!-- Injeção dinâmica de scripts baseada em consentimento -->
@foreach(config('scripts', []) as $categoryKey => $category)
    @foreach($category['items'] ?? [] as $itemKey => $item)
        <!-- 💡 Só renderiza tag <script> se o item de fato tiver uma URL externa ('src') -->
        @if(isset($item['src']))
            @if(($category['level'] ?? '') === 'required')
                <!-- Scripts Essenciais (Carregamento imediato) -->
                <script src="{{ $item['src'] }}" defer></script>
            @else
                <!-- Scripts Opcionais (Bloqueados até aceitação) -->
                <script type="text/plain" data-cookie-category="{{ $categoryKey }}" src="{{ $item['src'] }}"></script>
            @endif
        @endif
    @endforeach
@endforeach
