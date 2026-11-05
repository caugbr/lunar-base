<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Autenticação') - {{ setting('site_name', 'Lunar Base') }}</title>

    @php
    $skin = config('admin.skin');
    $varsFile = $skin === 'default' ? 'css/admin/vars.css' : "css/admin/skins/vars-{$skin}.css";
    @endphp
    <link rel="stylesheet" href="{{ asset($varsFile) }}">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">

    <meta name="robots" content="noindex, nofollow">

    {{-- Cada view pode injetar estilos adicionais aqui --}}
    @stack('styles')
</head>
<body class="@yield('body_class')" data-theme="light">
    <div class="login-container">
        <h1>
            <x-dynamic-component :component="'lucide-' . ($__env->yieldContent('icon') ?: 'lock')" class="lucid-icon" style="width: 20px; height: 20px;" />
            @hasSection('heading')
                <span>@yield('heading')</span>
            @else
                <a href="{{ route('home') }}" title="Ir para o site">{{ setting('site_name', 'Lunar Base') }}</a>
            @endif
        </h1>

        {{-- Erros gerais de validação --}}
        @if ($errors->any())
            <div class="error">
                {{ $errors->first() }}
            </div>
        @endif

        {{-- Mensagens de status (ex: "Link de redefinição enviado") --}}
        @if (session('status'))
            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 12px; border-radius: 6px; margin-bottom: 16px; font-size: 0.9rem;">
                {{ session('status') }}
            </div>
        @endif

        {{-- O formulário de cada página entra aqui --}}
        @yield('content')

        @yield('footer')
    </div>

    {{-- Cada view pode injetar scripts específicos aqui --}}
    @stack('scripts')
</body>
</html>
