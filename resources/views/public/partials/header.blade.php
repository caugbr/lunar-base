
<script>
    const savedTheme = localStorage.getItem('savedTheme');
    if (savedTheme) {
        document.body.dataset.theme = savedTheme;
        setTimeout(() => {
            const widget = document.querySelector('div#lunar-widget .lunar-widget');
            if (widget) {
                widget.dataset.theme = savedTheme;
            }
        }, 2000);
    }
</script>
<header class="site-header">
    <div class="container">
        <a href="{{ route('home') }}" class="logo">{{ setting('general.site_name') }}</a>

        <div class="right-section">
            <nav class="main-nav">
                @foreach($menu ?? [] as $item)
                    <a href="{{ $item['href'] }}" class="{{ $item['current_class'] }}">
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>

            <span class="header-links">
                <span class="switch-theme">
                    <a class="theme-light" href="#" title="Tema claro">
                        <x-lucide-sun class="lucid-icon" />
                    </a>
                    <a class="theme-dark" href="#" title="Tema escuro">
                        <x-lucide-moon class="lucid-icon" />
                    </a>
                </span>

                @auth
                    <a href="{{ route('admin.dashboard') }}" class="login" title="Admin">
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
