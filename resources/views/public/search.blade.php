@extends('public.site-layout')

@section('content')
<div class="search-index">
    <div class="container">

        <header class="search-header">
            <h1>Resultados da Busca</h1>
            @if($term)
                <p class="search-subtitle">
                    Exibindo <strong>{{ $total }}</strong> {{ $total === 1 ? 'resultado' : 'resultados' }} para: <em>"{{ $term }}"</em>
                </p>
            @endif

            {{-- Formulário no topo para permitir nova pesquisa --}}
            <div class="search-header-bar">
                <x-search />
            </div>
        </header>

        @if($results->count())
            <div class="search-grid">
                @foreach($results as $item)
                    <article class="search-card">

                        {{-- 1. Thumbnail (se existir relação e URL) --}}
                        @if(isset($item->thumbnail) && $item->thumbnail)
                            <a href="{{ $item->url ?? '#' }}" class="card-thumbnail">
                                <img src="{{ $item->thumbnail->thumb_url ?? $item->thumbnail->url ?? asset($item->thumbnail) }}" alt="{{ $item->_display_title }}">
                            </a>
                        @endif

                        <div class="card-body">
                            {{-- Badge indicando se é Post, Página, Curso, etc. --}}
                            <span class="card-type-badge">{{ $item->_type_label }}</span>

                            <h2>
                                <a href="{{ $item->url ?? '#' }}">{{ $item->_display_title }}</a>
                            </h2>

                            {{-- 2. Metadados (Autor, Data, Tempo de leitura) protegidos por @if --}}
                            @if(!empty($item->author_name) || !empty($item->published_at) || !empty($item->reading_time))
                                <div class="card-meta">
                                    @if(!empty($item->author_name))
                                        <span title="Publicado por {{ $item->author_name }}">
                                            <x-lucide-user class="lucid-icon" />
                                            {{ $item->author_name }}
                                        </span>
                                    @endif

                                    @if(!empty($item->published_at))
                                        <span title="Publicado em {{ \Carbon\Carbon::parse($item->published_at)->format('d/m/Y') }}">
                                            <x-lucide-calendar class="lucid-icon" />
                                            {{ \Carbon\Carbon::parse($item->published_at)->format('d/m/Y') }}
                                        </span>
                                    @endif

                                    @if(!empty($item->reading_time))
                                        <span title="Tempo de leitura">
                                            <x-lucide-clock class="lucid-icon" />
                                            {{ $item->reading_time }} min
                                        </span>
                                    @endif
                                </div>
                            @endif

                            {{-- 3. Resumo com fallback automático --}}
                            <p class="card-excerpt">{{ $item->_display_excerpt }}</p>

                            {{-- 4. Tags / Categorias (se o model tiver termos associados) --}}
                            @if(isset($item->terms) && $item->terms instanceof \Traversable && $item->terms->count())
                                <div class="card-tags">
                                    @foreach($item->terms as $termItem)
                                        @if(isset($termItem->taxonomy) && isset($termItem->slug))
                                            <a href="{{ url('/blog/' . $termItem->taxonomy->slug . '/' . $termItem->slug) }}">
                                                {{ $termItem->name }}
                                            </a>
                                        @else
                                            <span>{{ $termItem->name }}</span>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="search-pagination">
                {{ $results->links() }}
            </div>
        @else
            <div class="search-empty">
                <p>Nenhum resultado encontrado {{ $term ? 'para "' . $term . '"' : '' }}.</p>
                <small>Tente buscar por termos mais genéricos ou palavras-chave diferentes.</small>
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/public/blog.css') }}">
<style>
/* Ajustes específicos da busca que complementam o blog.css */
.card-type-badge {
    display: inline-block;
    background: #e2e8f0;
    color: #475569;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    padding: 2px 8px;
    border-radius: 4px;
    margin-bottom: 8px;
}
.search-header-bar {
    max-width: 500px;
    margin: 20px 0;
}
.search-empty {
    padding: 40px 0;
    color: #64748b;
}
</style>
@endpush
