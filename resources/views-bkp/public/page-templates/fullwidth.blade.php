@extends('public.empty-layout')

@section('content')
    <div class="page-fullwidth">
        <h1>{{ $page->title }}</h1>
        @if($page->excerpt)
            <p class="page-excerpt">{{ $page->excerpt }}</p>
        @endif
        <div class="page-content">
            {!! $page->content !!}
        </div>
    </div>
@endsection

@push('styles')
<style>
    .page-fullwidth {
        max-width: 1200px;
        margin: 0 auto;
        padding: 2rem;
    }
    .page-excerpt {
        font-size: 1.2rem;
        color: #666;
        margin-bottom: 2rem;
    }
    .page-content {
        line-height: 1.6;
    }
</style>
@endpush
