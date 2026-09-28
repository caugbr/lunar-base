<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Nova Senha - {{ setting('site_name', 'Lunar Base') }}</title>
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
            <x-lucide-lock class="lucid-icon" style="width: 20px; height: 20px;" />
            <span>Nova Senha</span>
        </h1>

        @if ($errors->any())
            <div class="error">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">

            <div class="form-group">
                <label for="email"><x-lucide-mail class="lucid-icon" /> E-mail</label>
                <input type="email" name="email" id="email" value="{{ old('email', $email) }}" required readonly style="opacity: 0.7; cursor: not-allowed;">
            </div>

            <div class="form-group">
                <label for="password"><x-lucide-key class="lucid-icon" /> Nova Senha (mín. 8 caracteres)</label>
                <span class="pwd">
                    <input type="password" name="password" id="password" required autofocus>
                    <x-lucide-eye class="lucid-icon show" />
                    <x-lucide-eye-off class="lucid-icon hide" />
                </span>
            </div>

            <div class="form-group">
                <label for="password_confirmation"><x-lucide-key class="lucid-icon" /> Confirmar Nova Senha</label>
                <span class="pwd">
                    <input type="password" name="password_confirmation" id="password_confirmation" required>
                    <x-lucide-eye class="lucid-icon show" />
                    <x-lucide-eye-off class="lucid-icon hide" />
                </span>
            </div>

            <button type="submit">Salvar Nova Senha</button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('span.pwd').forEach(pwd => {
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
        });
    </script>
</body>
</html>
