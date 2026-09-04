@if ($paginator->hasPages())
    <div class="admin-pagination">
        {{-- Versão Mobile --}}
        <div class="pagination-mobile">
            @if ($paginator->onFirstPage())
                <span class="pagination-prev disabled">← Anterior</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="pagination-prev">← Anterior</a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="pagination-next">Próximo →</a>
            @else
                <span class="pagination-next disabled">Próximo →</span>
            @endif
        </div>

        {{-- Versão Desktop --}}
        <div class="pagination-desktop">
            <div class="pagination-info">
                Mostrando {{ $paginator->firstItem() }} a {{ $paginator->lastItem() }} de {{ $paginator->total() }} resultados
            </div>

            <div class="pagination-links">
                {{-- Anterior --}}
                @if ($paginator->onFirstPage())
                    <span class="disabled">←</span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev">←</a>
                @endif

                {{-- Links das páginas --}}
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="disabled">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="active">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Próximo --}}
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next">→</a>
                @else
                    <span class="disabled">→</span>
                @endif
            </div>
        </div>
    </div>
@endif
