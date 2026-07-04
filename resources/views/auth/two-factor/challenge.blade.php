<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificação de duas etapas - {{ setting('site_name', 'Lunar Base') }}</title>
    <link rel="stylesheet" href="{{ asset('css/admin/vars.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body>
    <div class="login-container">
        <h1>
            <x-lucide-shield-check class="lucid-icon" />
            Verificação
        </h1>

        @if ($errors->any())
            <div class="error">
                {{ $errors->first() }}
            </div>
        @endif

        @if (session('status'))
            <div class="status-box">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ url('/two-factor/challenge') }}">
            @csrf

            <div class="form-group">
                <label for="code"><x-lucide-smartphone class="lucid-icon" /> Código do aplicativo</label>
                <input type="text" name="code" id="code" maxlength="6" inputmode="numeric" pattern="[0-9]*" required autofocus autocomplete="one-time-code">
            </div>

            <button type="submit"><x-lucide-arrow-right class="lucid-icon" /> Verificar</button>
        </form>

        <div class="info">
            Abra seu aplicativo autenticador e digite o código
        </div>

        <form method="POST" action="{{ route('two-factor.send-email') }}" id="by-mail">
            @csrf
            <button type="submit">
                <x-lucide-mail class="lucid-icon" />
                Enviar código por e-mail
            </button>
        </form>
    </div>
</body>
</html>
