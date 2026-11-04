<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - {{ setting('site_name', 'Lunar Base') }}</title>
    @php
    $skin = config('admin.skin');
    $varsFile = $skin === 'default' ? 'css/admin/vars.css' : "css/admin/skins/vars-{$skin}.css";
    @endphp
    <link rel="stylesheet" href="{{ asset($varsFile) }}">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">

    <meta name="robots" content="noindex, nofollow">

    {{-- Estilos injetados pelos componentes de form (ex: toggle de senha) --}}
    @stack('styles')

    <style>
        /* Oculta a barra de força da senha especificamente na tela de login */
        .login-container .password-strength-meter {
            display: none !important;
        }
        /* Garante que o botão de submit ocupe a largura total */
        .login-btn {
            width: 100%;
            justify-content: center;
        }
    </style>

    <script>
        // Se a página foi restaurada do cache de memória do celular (BFCache), força um reload limpo
        window.addEventListener('pageshow', function (event) {
            if (event.persisted) {
                window.location.reload();
            }
        });
    </script>
</head>
@php
$bodyClass = '';
if (isset($_GET['interim'])) {
    $bodyClass = ' class="interim"';
}
@endphp
<body{!! $bodyClass !!} data-theme="light">
    <div class="login-container">
        <h1>
            <x-lucide-lock class="lucid-icon" style="width: 20px; height: 20px;" />
            <a href="{{ route('home') }}" title="Ir para o site">{{ setting('site_name', 'Lunar Base') }}</a>
        </h1>

        @if ($errors->any())
            <div class="error">
                {{ $errors->first() }}
            </div>
        @endif

        @if (session('status'))
            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 12px; border-radius: 6px; margin-bottom: 16px; font-size: 0.9rem;">
                {{ session('status') }}
            </div>
        @endif

        {{-- Mensagem contextual de Reautenticação --}}
        @if(request()->filled('interim'))
            <div class="interim-notice" style="background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; padding: 12px; border-radius: 6px; margin-bottom: 16px;">
                <strong>Sua sessão expirou por inatividade.</strong><br>
                Faça login novamente e continue de onde parou.
            </div>
        @endif

        {{-- Componente de Formulário: CSRF e prevenção de duplo submit já inclusos --}}
        <x-form
            method="POST"
            :action="route('login')"
            :target="!request()->has('interim') ? '_top' : null"
        >
            @if(request()->filled('interim'))
                <input type="hidden" name="interim" value="1">
            @endif

            {{-- Campo de E-mail --}}
            <x-form.text
                type="email"
                name="email"
                label="E-mail"
                :value="old('email')"
                required
                autofocus
            />

            {{-- Campo de Senha com o botão de exibir/ocultar integrado --}}
            {{-- <x-form.text
                type="password"
                name="password"
                label="Senha"
                required
            /> --}}
            <x-form.password
                name="password"
                label="Senha"
                required
            />

            @if(setting('auth.use_captcha'))
            @php
                $siteKey = setting('auth.turnstile_site_key', '');
                $appearance = setting('auth.turnstile_appearance', 'always');
                $size = setting('auth.turnstile_size', 'normal');
            @endphp
            <div class="captcha-wrapper">
                <div
                    class="cf-turnstile"
                    data-sitekey="{{ $siteKey }}"
                    data-theme="light"
                    data-size="{{ $size }}"
                    data-appearance="{{ $appearance }}"
                ></div>
            </div>
            @endif

            <div style="text-align: right; margin-top: -8px; margin-bottom: 16px;">
                <a href="{{ route('password.request') }}" style="font-size: 0.85rem; color: var(--text-muted, #64748b); text-decoration: none;">
                    Esqueceu a senha?
                </a>
            </div>

            {{-- Botão de submit: desabilitado e com spinner ativado pelo <x-form> --}}
            <x-form.button
                type="submit"
                icon="log-in"
                class="login-btn"
            >
                Entrar
            </x-form.button>
        </x-form>

        <div class="info">
            @php
                $footerMsg = config('site.loginFooterText', 'Acesso restrito');
            @endphp
            {{ $footerMsg }}
        </div>
    </div>

    {{-- Scripts injetados pelos componentes de form (duplo submit, toggle de senha, etc.) --}}
    @stack('scripts')

    @if(setting('auth.use_captcha'))
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    @endif
</body>
</html>
