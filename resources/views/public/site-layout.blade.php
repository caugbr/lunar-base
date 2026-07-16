<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <x-seo-meta />

    @if(setting('general.cookies_consent'))
    <x-cookie.scripts />
    @endif

    <x-hook name="main.head" />

    <link rel="stylesheet" href="{{ asset('css/public/site.css') }}">
    <script src="{{ asset('js/site.js') }}"></script>
    @stack('styles')
</head>

<body{!! $theme !!}>
    @include('public.partials.header')

    <x-hook name="main.before_content" />

    <main class="site-content">
        <div class="container">
            @if(setting('navigation.breadcrumbs'))
            <x-breadcrumbs :icon="setting('navigation.breadcrumbs_icon')" />
            @endif
        </div>

        <x-hook name="main.after_breadcrumbs" />

        @yield('content')
    </main>

    <x-hook name="main.after_content" />

    @include('public.partials.footer')

    @if(setting('general.cookies_consent'))
    <x-cookie.banner />
    @endif

    @stack('footer-styles')
    @stack('scripts')
</body>
</html>
