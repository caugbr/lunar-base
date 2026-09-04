<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permissão negada - {{ setting('general.site_name') }}</title>
    @php
    $skin = config('admin.skin');
    $varsFile = $skin === 'default' ? 'css/admin/vars.css' : "css/admin/skins/vars-{$skin}.css";
    @endphp
    <link rel="stylesheet" href="{{ asset($varsFile) }}">
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
                <strong>ERRO 403</strong>
                Permissão negada
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
