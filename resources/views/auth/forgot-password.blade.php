<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Senha - {{ setting('site_name', 'Lunar Base') }}</title>
    @php
    $skin = config('admin.skin');
    $varsFile = $skin === 'default' ? 'css/admin/vars.css' : "css/admin/skins/vars-{$skin}.css";
    @endphp
    <link rel="stylesheet" href="{{ asset($varsFile) }}">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">

    <meta name="robots" content="noindex, nofollow">
</head>
<body>
    <div class="login-container">
        <h1>
            <x-lucide-key-round class="lucid-icon" style="width: 20px; height: 20px;" />
            <span>Recuperar Senha</span>
        </h1>

        <p style="font-size: 0.9rem; color: var(--text-muted, #64748b); margin-bottom: 20px;">
            Informe o e-mail da sua conta e enviaremos um link para você cadastrar uma nova senha.
        </p>

        @if (session('status'))
            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 12px; border-radius: 6px; margin-bottom: 16px; font-size: 0.9rem;">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="error">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="form-group">
                <label for="email"><x-lucide-mail class="lucid-icon" /> E-mail</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus>
            </div>

            <button type="submit">Enviar Link de Recuperação</button>
        </form>

        <div class="info" style="margin-top: 20px;">
            <a href="{{ route('login') }}" style="color: inherit; text-decoration: underline;">Voltar para o login</a>
        </div>
    </div>
</body>
</html>
