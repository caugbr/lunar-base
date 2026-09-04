
<header class="site-header">
    <div class="container">
        <a href="{{ route('home') }}" class="logo">{{ setting('general.site_name') }}</a>

        <div class="right-section">
            <nav class="main-nav">
                <x-hook name="public.main_menu" desc="Menu princial, no header do site">
                @foreach($menu ?? [] as $item)
                    <a href="{{ $item['href'] }}" class="{{ $item['current_class'] }}">
                        {{ $item['label'] }}
                    </a>
                @endforeach
                </x-hook>
            </nav>

            <span class="header-links">
            @auth
                <a href="{{ route('admin.dashboard.index') }}" class="login" title="Admin">
                    <x-lucide-settings class="lucid-icon" />
                </a>
            @else
                {{-- Usuário NÃO está logado: Mostrar link de Login --}}
                <a href="{{ route('login') }}" class="login" title="Login">
                    <x-lucide-settings class="lucid-icon" />
                </a>
            @endauth
            </span>

            <button class="menu-toggle" aria-label="Menu" onclick="document.querySelector('.main-nav').classList.toggle('open')">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </div>
</header>
