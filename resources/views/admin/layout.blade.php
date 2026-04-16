<!DOCTYPE html>
@php
    $info = config('admin');
    $page_title = $info['page_title'] ?? 'Lunar Admin';
    $title = $info['title'] ?? 'Lunar Admin';
    $subtitle = $info['subtitle'] ?? 'Painel de Controle';
    $logo = $info['logo'] ?? '🌙';
    $colors = $info['colors'] ?? [];
    $sortedMenu = collect($info['menu'] ?? [])->sortBy('order');
@endphp
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page_title }}</title>

    {{-- Cores dinâmicas via CSS variables
    <style>
        :root {
        @foreach($colors as $key => $color)
            --color-{{ $key }}: {{ $color }};
        @endforeach

        }
    </style> --}}

    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="{{ asset('js/admin.js') }}"></script>
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
                <h2>
                    @if(filter_var($logo, FILTER_VALIDATE_URL) || preg_match('/\.(png|jpg|jpeg|gif|svg)$/i', $logo))
                        <img src="{{ asset($logo) }}" alt="{{ $title }}" class="admin-logo-img">
                    @else
                        {{ $logo }}
                    @endif
                    {{ $title }}
                </h2>
                <p>{{ $subtitle }}</p>
            </div>

            <nav class="admin-nav">
                @foreach($sortedMenu as $item)
                    @php
                        $isActive = request()->routeIs($item['active']);
                    @endphp
                    <a href="{{ route($item['route']) }}"
                       class="admin-nav-item {{ $isActive ? 'active' : '' }}">
                        <x-dynamic-component :component="'lucide-' . $item['icon']" class="lucid-icon" />
                        <span>{{ $item['label'] }}</span>
                    </a>
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
                @if(session('success'))
                    <div class="admin-alert admin-alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="admin-alert admin-alert-error">
                        {{ $errors->first() }}
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>
    @stack('scripts')
</body>
</html>
