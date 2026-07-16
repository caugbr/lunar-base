<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - {{ setting('general.site_name') }}</title>

    @php
    $skin = config('admin.skin');
    $varsFile = $skin === 'default' ? 'css/admin/vars.css' : "css/admin/skins/vars-{$skin}.css";
    @endphp
    <link rel="stylesheet" href="{{ asset($varsFile) }}">
    <link rel="stylesheet" href="{{ asset('css/admin/admin.css') }}">

    <script src="{{ asset('js/admin.js') }}"></script>

    @if(setting('navigation.save_search_params'))
    <script src="{{ asset('js/preserve-search.js') }}"></script>
    @endif

    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    @stack('styles')
</head>
<body data-theme="{{ setting('general.admin_theme', 'light') }}">
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <button class="admin-menu-toggle" id="menuToggle">
            <span></span> <span></span> <span></span>
        </button>
        <aside class="admin-sidebar">
            <div class="admin-sidebar-header">
                @php $title = setting('general.site_name'); @endphp
                <h2>
                    <a href="{{ route('home') }}">{{ $title }}</a>
                </h2>
                <p>Painel Administrativo</p>
            </div>

            @include('admin.partials.menu')

            <div class="system-assign">
                {{ config('app.name') }}
                {{ config('app.version') }}
            </div>
        </aside>

        <!-- Conteúdo Principal -->
        <main class="admin-main">
            <div class="admin-header">
                <div class="admin-header-title">
                    <h1>@yield('header_title', 'Dashboard')</h1>
                    <p>@yield('header_subtitle', 'Bem-vindo ao painel de controle')</p>
                </div>
                <div class="admin-header-user">
                    <a href="{{ route('admin.profile.edit') }}">
                        <x-hook name="admin.header_user_avatar" :params="['user' => auth()->user()]" desc="Substitui o avatar do usuário">
                            <x-lucide-user-pen class="lucid-icon" style="vertical-align: baseline" />
                        </x-hook>
                        {{ auth()->user()->name }}
                    </a>
                    <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                        @csrf
                        <button type="submit" class="admin-btn admin-btn-secondary" style="padding: 6px 12px;">
                            <x-lucide-log-out class="lucid-icon" /> Sair
                        </button>
                    </form>
                    <x-admin-help />
                </div>
            </div>

            <div class="admin-content">
                <x-admin-alert />

                @yield('content')
            </div>
        </main>
    </div>
    @stack('scripts')
</body>
</html>
