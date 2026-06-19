<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>
        document.cookie = 'laravel_session=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
        document.cookie = 'XSRF-TOKEN=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
    </script>
    <title>Sessão expirada - {{ setting('general.site_name') }}</title>
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

        .button {
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
            text-decoration: none;
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
    </style>
</head>
<body>
    <div class="login-container">
        <h1>
            <a href="{{ route('home') }}">
                <x-lucide-lock class="lucid-icon" style="width: 20px; height: 20px;" />
                {{ setting('general.site_name') }}
            </a>
        </h1>

        @if ($errors->any())
            <div class="error">
                {{ $errors->first() }}
            </div>
        @else
            <div class="error">
                Sessão expirada, faça o login novamente
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
