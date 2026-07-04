@extends('public.site-layout')

@section('title', $page->title)
@section('meta_description', $page->excerpt ?? 'Lunar Apps — Astrologia Embedável')

@section('content')
<div class="container page-container">
    <div class="page-header">
        <h1>{{ $page->title }}</h1>
        @if($page->excerpt)
            <p class="page-excerpt">{{ $page->excerpt }}</p>
        @endif
    </div>

    <x-hook name="page.before_content" :params="['page' => $page]" />

    <div class="page-content">
        {!! $page->content !!}
    </div>

    <x-hook name="page.after_content" :params="['page' => $page]" />

    @if($page->updated_at)
        <div class="page-footer">
            <small>Última atualização: {{ $page->updated_at->format('d/m/Y H:i') }}</small>
        </div>
    @endif

    <x-hook name="page.after_footer" :params="['page' => $page]" />
</div>
@endsection
