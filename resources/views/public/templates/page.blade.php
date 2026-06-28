@extends('public.layout')

@section('title', $page->title)

@section('content')
<div class="container">
    <div class="page-header">
        <h1>{{ $page->title }}</h1>
        {{-- @if($page->excerpt)
            <p class="excerpt">{{ $page->excerpt }}</p>
        @endif --}}
    </div>

    <div class="page-content">
        {!! $page->content !!}
    </div>
    <div class="page-footer">
        <small>Última atualização: {{ $page->updated_at->format('d/m/Y H:i') }}</small>
    </div>
</div>
@endsection
