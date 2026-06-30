<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página não encontrada - {{ setting('general.site_name') }}</title>
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
                <strong>ERRO 404</strong>
                A página procurada não foi encontrada
            </div>
        @endif

        @php
        $isAdmin = str_starts_with(request()->path(), 'admin');
        @endphp

        <p>
            @if($isAdmin)
            <a class="button" href="{{ route('admin.dashboard') }}">
                <x-lucide-layout-dashboard class="lucid-icon" />
                Dashboard
            </a>
            @else
            <a class="button" href="{{ route('home') }}">
                <x-lucide-house class="lucid-icon" />
                Home
            </a>
            @endif
        </p>
    </div>
</body>
</html>
