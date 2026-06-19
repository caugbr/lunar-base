<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - {{ setting('site_name', 'Lunar Base') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }

        .login-container h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
            font-size: 1.8rem;
        }

        .login-container h1 a {
            color: #333;
            text-decoration: none;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }

        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
            display: flex;
            gap: 0.5rem;
            justify-content: center;
        }

        button:hover {
            transform: translateY(-2px);
        }

        .error {
            background: #fee;
            border: 1px solid #fcc;
            color: #c33;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }

        .info {
            text-align: center;
            margin-top: 20px;
            font-size: 0.9rem;
            color: #666;
        }

        .lucid-icon {
            width: 20px;
            height: 20px;
            vertical-align: middle
        }

        .pwd {
            display: inline-block;
            width: 100%;
            padding: 0;
            margin: 0;
            position: relative;
        }

        .pwd .lucid-icon {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            cursor: pointer;
        }

        .pwd .hide {
            display: none;
        }

        .pwd.visible .hide {
            display: inline;
        }

        .pwd.visible .show {
            display: none;
        }

        .pwd input {
            padding-right: 32px;
        }
    </style>
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
                    data-sitekey="{{ config('services.turnstile.site_key') }}"
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

    @if(setting('navigation.use_captcha'))
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    @endif
</body>
</html>
