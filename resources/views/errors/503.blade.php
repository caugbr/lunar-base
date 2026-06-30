<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Serviço indisponível - {{ setting('general.site_name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/errors.css') }}">
</head>
<body>
    <div class="login-container">
        <h1>
            <a href="{{ route('home') }}">
                <x-lucide-circle-alert class="lucid-icon" style="width: 20px; height: 20px;" />
                {{ setting('general.site_name') }}
            </a>
        </h1>

        @if ($errors->any())
            <div class="error">
                {{ $errors->first() }}
            </div>
        @else
            <div class="error">
                <strong>ERRO 503</strong>
                Serviço indisponível
            </div>
        @endif

        <p>
            <a class="button" href="{{ route('login') }}">
                <x-lucide-log-in class="lucid-icon" />
                Login
            </a>
        </p>
    </div>
</body>
</html>
