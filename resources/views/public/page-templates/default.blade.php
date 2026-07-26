@extends('public.site-layout')

@section('title', $page->title)
@section('meta_description', $page->excerpt ?? '')

@section('content')
<div class="container page-container">
    <div class="page-header">
        <h1>{{ $page->title }}</h1>
        <x-hook name="page.after_title" :params="['page' => $page]" desc="Depois do título da página" />
        @if($page->excerpt)
            <p class="page-excerpt">{{ $page->excerpt }}</p>
        @endif
        <x-hook name="page.header_end" :params="['page' => $page]" desc="No final do header da página" />
    </div>

    <x-hook name="page.before_content" :params="['page' => $page]" desc="Antes do conteúdo da página" />

    <div class="page-content">
        {!! $page->content !!}
    </div>

    <x-hook name="page.after_content" :params="['page' => $page]" desc="Depois do conteúdo da página" />

    @if($page->updated_at)
        <div class="page-footer">
            <small>Última atualização: {{ $page->updated_at->format('d/m/Y H:i') }}</small>
        </div>
    @endif

    <x-hook name="page.after_footer" :params="['page' => $page]" desc="Abaixo do footer da página" />
</div>
@endsection
