<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificação de E-mail - {{ setting('site_name', 'Lunar Base') }}</title>
    @php
    $skin = config('admin.skin');
    $varsFile = $skin === 'default' ? 'css/admin/vars.css' : "css/admin/skins/vars-{$skin}.css";
    @endphp
    <link rel="stylesheet" href="{{ asset($varsFile) }}">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body>
    <div class="login-container">
        <h1>
            <x-lucide-mail class="lucid-icon" style="width: 20px; height: 20px;" />
            <a href="{{ route('home') }}">{{ setting('site_name', 'Lunar Base') }}</a>
        </h1>

        @if (session('status') == 'verification-link-sent' || session('success'))
            <div class="success-message" style="color: #10b981; background-color: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.25); padding: 0.75rem 1rem; border-radius: 6px; font-size: 0.875rem; margin-bottom: 1.25rem; text-align: center;">
                Um novo link de verificação foi enviado para o seu e-mail!
            </div>
        @endif

        @if ($errors->any())
            <div class="error">
                {{ $errors->first() }}
            </div>
        @endif

        <p style="font-size: 0.875rem; line-height: 1.6; margin-bottom: 1.5rem; text-align: center; color: var(--color-text-muted, #6B6880);">
            Obrigado por se cadastrar! Antes de acessar o sistema, por favor confirme seu e-mail clicando no link que enviamos para a sua caixa de entrada.
        </p>

        {{-- Formulário para reenviar o e-mail de verificação --}}
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit">
                <x-lucide-send class="lucid-icon" /> Reenviar E-mail de Verificação
            </button>
        </form>

        {{-- Botão para o usuário poder encerrar a sessão caso queira --}}
        <form method="POST" action="{{ route('logout') }}" style="margin-top: 1rem; text-align: center;">
            @csrf
            <button type="submit" style="background: none; border: none; color: var(--color-text-muted, #6B6880); font-size: 0.8rem; cursor: pointer; text-decoration: underline; padding: 0;">
                Sair da conta
            </button>
        </form>

        <div class="info">
            Verificação de e-mail obrigatória
        </div>
    </div>
</body>
</html>
