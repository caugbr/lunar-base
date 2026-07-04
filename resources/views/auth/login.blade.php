<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - {{ setting('site_name', 'Lunar Base') }}</title>
    <link rel="stylesheet" href="{{ asset('css/admin/vars.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body>
    <div class="login-container">
        <h1>
            <x-lucide-lock class="lucid-icon" style="width: 20px; height: 20px;" />
            <a href="{{ route('home') }}">{{ setting('site_name', 'Lunar Base') }}</a>
        </h1>

        @if ($errors->any())
            <div class="error">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-group">
                <label for="email"><x-lucide-mail class="lucid-icon" /> E-mail</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus>
            </div>

            <div class="form-group">
                <label for="password"><x-lucide-key class="lucid-icon" /> Senha</label>
                <span class="pwd">
                    <input type="password" name="password" id="password" required>
                    <x-lucide-eye class="lucid-icon show" />
                    <x-lucide-eye-off class="lucid-icon hide" />
                </span>
            </div>

            @if(setting('navigation.use_captcha'))
            <div class="captcha-wrapper">
                <div
                    class="cf-turnstile"
                    data-sitekey="{{ setting('auth.turnstile_site_key') }}"
                    data-theme="light"
                    data-size="normal"
                    data-callback="onSuccess"
                ></div>
            </div>
            @endif

            <button type="submit"><x-lucide-log-in class="lucid-icon" /> Entrar</button>
        </form>

        <div class="info">
            Acesso restrito
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const pwd = document.querySelector('span.pwd');
            const input = pwd.querySelector('input');
            const eyes = pwd.querySelectorAll('.lucid-icon');

            eyes.forEach(eye => {
                eye.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (input.type === 'password') {
                        input.type = 'text';
                        pwd.classList.add('visible');
                    } else {
                        input.type = 'password';
                        pwd.classList.remove('visible');
                    }
                });
            });
        });
    </script>

    @if(setting('auth.use_captcha'))
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    @endif
</body>
</html>
