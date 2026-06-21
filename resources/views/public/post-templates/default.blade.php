{{-- resources/views/public/post-templates/default.blade.php --}}

@extends('public.site-layout')

@section('content')
<article class="post-single">
    <div class="container">

        <header class="post-header">
            @if($post->thumbnail)
                <div class="post-thumbnail">
                    <img src="{{ $post->thumbnail->url }}" alt="{{ $post->title }}">
                </div>
            @endif

            <div class="post-meta-top">
                <h1>{{ $post->title }}</h1>

                <div class="post-meta">
                    <span class="meta-author">
                        <x-lucide-user class="lucid-icon" />
                        {{ $post->author_name }}
                    </span>
                    <span class="meta-date">
                        <x-lucide-calendar class="lucid-icon" />
                        {{ $post->published_at->format('d \d\e F \d\e Y') }}
                    </span>
                    <span class="meta-reading-time">
                        <x-lucide-clock class="lucid-icon" />
                        {{ $post->reading_time }} min de leitura
                    </span>
                    @if(setting('reading.post_use_reaction', false))
                    <span class="meta-reaction-links">
                        <x-reaction-links type="post" :id="$post->id" :data="$reactionData" />
                    </span>
                    @endif
                </div>
            </div>
        </header>

        <div class="post-content">
            {!! $post->content !!}
        </div>

        <footer class="post-footer">
            @if($post->terms->count())
                <div class="post-tags">
                    <x-lucide-tag class="lucid-icon" />
                    @foreach($post->terms as $term)
                        <a href="{{ url('/blog/' . $term->taxonomy->slug . '/' . $term->slug) }}" class="tag-link">
                            {{ $term->name }}
                        </a>
                    @endforeach
                </div>
            @endif

            <div class="post-nav">
                <a href="{{ url('/blog') }}" class="back-to-blog">
                    <x-lucide-arrow-left class="lucid-icon" />
                    Voltar ao blog
                </a>
            </div>
        </footer>
    </div>
</article>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/blog.css') }}">
@endpush
