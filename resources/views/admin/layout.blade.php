<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Lunar API</title>
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <script src="{{ asset('js/admin.js') }}"></script>

    @if(setting('navigation.save_search_params'))
    <script src="{{ asset('js/preserve-search.js') }}"></script>
    @endif

    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    @stack('styles')
</head>
<body>
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

            <nav class="admin-nav">
                @php
                    $menuGroups = config('adminMenu.menu', []);
                @endphp

                @foreach($menuGroups as $group)
                    {{-- Título da seção --}}
                    @if(isset($group['title']))
                        <div class="admin-nav-section">
                            <span class="section-title">{{ $group['title'] }}</span>
                        </div>
                    @endif

                    {{-- Itens da seção --}}
                    @foreach($group['items'] ?? [] as $item)
                        @php
                            $isActive = request()->routeIs($item['active']);
                        @endphp
                        <a href="{{ route($item['route']) }}"
                        class="admin-nav-item {{ $isActive ? 'active' : '' }}">
                            <x-dynamic-component :component="'lucide-' . $item['icon']" class="lucid-icon" />
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                @endforeach
            </nav>
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
                        {{ auth()->user()->name }}
                    </a>
                    <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                        @csrf
                        <button type="submit" class="admin-btn admin-btn-secondary" style="padding: 6px 12px;">
                            <x-lucide-log-out class="lucid-icon" /> Sair
                        </button>
                    </form>
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
