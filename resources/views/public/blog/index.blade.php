@extends('public.site-layout')

@section('content')
<div class="blog-index">
    <div class="container">

        <header class="blog-header">
            <h1>Blog</h1>
        </header>

        @if($posts->count())
            <div class="blog-grid">
                @foreach($posts as $post)
                    <article class="blog-card">
                        @if($post->thumbnail)
                            <a href="{{ $post->url }}" class="card-thumbnail">
                                <img src="{{ $post->thumbnail->thumb_url }}" alt="{{ $post->title }}">
                            </a>
                        @endif

                        <div class="card-body">
                            <h2><a href="{{ $post->url }}">{{ $post->title }}</a></h2>

                            <div class="card-meta">
                                <span title="Publicado por {{ $post->author_name }}">
                                    <x-lucide-user class="lucid-icon" />
                                    {{ $post->author_name }}
                                </span>
                                <span title="Publicado em {{ $post->published_at->format('d/m/Y') }}">
                                    <x-lucide-calendar class="lucid-icon" />
                                    {{ $post->published_at->format('d/m/Y') }}
                                </span>
                                <span title="Tempo de leitura">
                                    <x-lucide-clock class="lucid-icon" />
                                    {{ $post->reading_time }} min
                                </span>
                            </div>

                            <p class="card-excerpt">{{ $post->excerpt }}</p>

                            @if($post->terms->count())
                                <div class="card-tags">
                                    @foreach($post->terms as $term)
                                        <a href="{{ url('/blog/' . $term->taxonomy->slug . '/' . $term->slug) }}">
                                            {{ $term->name }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="blog-pagination">
                {{ $posts->links() }}
            </div>
        @else
            <p class="blog-empty">Nenhuma publicação ainda.</p>
        @endif
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/blog.css') }}">
@endpush
