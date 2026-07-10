@extends('public.empty-layout')

@section('content')
    <div class="container-with-sidebar">
        <aside class="sidebar">
            @yield('sidebar')
        </aside>
        <main class="main-content">
            <h1>{{ $page->title }}</h1>
            <div class="page-content">
                {!! $page->content !!}
            </div>
        </main>
    </div>
@endsection
