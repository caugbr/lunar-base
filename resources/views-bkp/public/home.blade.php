@extends('public.site-layout')

@section('title', 'Documentação Técnica de Arquitetura — Lunar Base')
@section('meta_description', 'Guia de referência detalhado sobre o ecossistema, helpers, traits e configurações do Lunar Base Starter Kit.')

@section('content')
<div class="lunar-logo">
    <img src="{{ asset('images/lunar-base.png') }}" alt="Lunar Base - Laravel Starter Kit">
</div>

<div class="lunar-doc-container">

    <header class="lunar-doc-header">
        {{-- <h1 class="lunar-doc-title">Lunar Base</h1> --}}
        <p class="lunar-doc-lead">
            <strong>Lunar Base</strong> é um Starter Kit para Laravel, com
            jeito de CMS.
        </p>
    </header>

    <x-render name="featured_posts" />

    {{-- @if($featuredPosts->count())
        <div class="blog-grid">
            @foreach($featuredPosts as $post)
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
    @endif --}}

@endsection

@push('styles')
<style>
.lunar-logo {
    text-align: center;
    width: 400px;
    margin: auto;
    border: 10px solid #333;
    border-radius: 50px;
    overflow: hidden;
}

.lunar-logo img {
    max-width: 100%;
    object-fit: cover;
    display: block;
}

.lunar-doc-container {
    text-align: center;
    max-width: 900px;
    margin: 0 auto;
    /* padding: 50px 20px 0; */
}
</style>
@endpush
