<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificação de dois fatores - {{ setting('site_name', 'Lunar Base') }}</title>
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
            text-align: center;
            letter-spacing: 0.5em;
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

        .remaining {
            text-align: center;
            font-size: 0.85rem;
            color: #888;
            margin-top: 8px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>
            <x-lucide-shield class="lucid-icon" style="width: 20px; height: 20px;" />
            Verificação de dois fatores
        </h1>

        @if ($errors->any())
            <div class="error">
                {{ $errors->first() }}
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
            Abra seu aplicativo autenticador e digite o código de 6 dígitos.
        </div>

        <form method="POST" action="{{ route('two-factor.send-email') }}">
            @csrf
            <button type="submit">Enviar código por e-mail</button>
        </form>
    </div>
</body>
</html>
