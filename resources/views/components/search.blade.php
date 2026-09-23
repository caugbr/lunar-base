<form action="{{ $action }}" method="GET" class="search-form">
    <div class="search-input-wrapper">
        <input
            type="search"
            name="q"
            value="{{ request('q') }}"
            placeholder="{{ $placeholder }}"
            class="search-input"
            autocomplete="off"
            required
        >

        {{-- Se tipos customizados foram passados, envia via hidden fields --}}
        @if(!empty($types))
            @foreach($types as $type)
                <input type="hidden" name="types[]" value="{{ $type }}">
            @endforeach
        @endif

        <button type="submit" class="search-button" title="Pesquisar">
            <x-lucide-search class="lucid-icon" />
        </button>
    </div>
</form>
