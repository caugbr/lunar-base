<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <x-seo-meta />

    @if(setting('general.cookie_consent'))
    <x-cookie.scripts />
    @endif

    <link rel="stylesheet" href="{{ asset('css/site.css') }}">
    <script src="{{ asset('js/site.js') }}"></script>
    @stack('styles')
</head>
@php
$theme = setting('site_theme') ?? '';
if ($theme) {
    $theme = " data-theme=\"{$theme}\"";
}
@endphp
<body{!! $theme !!}>
    @include('public.partials.header')
{{-- <x-text-size /> --}}
    <main class="site-content">
        <div class="container">
            <x-breadcrumbs />
        </div>
        @yield('content')
    </main>

    @include('public.partials.footer')

    @if(setting('general.cookie_consent'))
    <x-cookie.banner />
    @endif

    @stack('footer-styles')
    @stack('scripts')
</body>
</html>
