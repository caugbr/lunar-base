{{-- Template Name: Custom Theme Default --}}
@extends('public.site-layout')

@section('content')
<div class="theme-test-container" style="background-color: var(--color-bg-card); color: var(--color-text); padding: var(--space-md); border-radius: 12px; border: 1px solid var(--color-border); font-family: var(--font-body);">

    <!-- Marcação visual para sabermos que o tema está ativo -->
    <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 2px; color: var(--color-primary); margin-bottom: var(--space-xs); font-weight: bold;">
        [ Rendered by: Test Theme ]
    </div>

    <h1 style="font-family: var(--font-heading); font-size: 2.25rem; margin-bottom: var(--space-sm); color: var(--color-text);">
        {{ $post->title }}
    </h1>

    <div style="line-height: 1.6; margin-bottom: var(--space-md); color: var(--color-text);">
        {!! $post->content !!}
    </div>

    <!-- Seção de Comentários do nosso Plugin anterior -->
    @if(array_key_exists('comments', view()->getFinder()->getHints()))
        @include('comments::comments-area', ['model' => $post])
    @endif
</div>
@endsection
