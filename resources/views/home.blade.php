<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lunar Widgets - Widgets astrológicos interativos</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .home-container {
            text-align: center;
            padding: 40px 20px;
            max-width: 800px;
            margin: 0 auto;
        }

        .logo {
            font-size: 4rem;
            margin-bottom: 20px;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        h1 {
            font-size: 3rem;
            margin-bottom: 16px;
            font-weight: 700;
            text-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }

        .tagline {
            font-size: 1.3rem;
            margin-bottom: 40px;
            opacity: 0.95;
        }

        .description {
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 48px;
            opacity: 0.9;
        }

        .buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-block;
            padding: 14px 32px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 1rem;
        }

        .btn-primary {
            background: white;
            color: #667eea;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
        }

        .btn-secondary {
            background: transparent;
            color: white;
            border: 2px solid white;
        }

        .btn-secondary:hover {
            background: rgba(255,255,255,0.1);
            transform: translateY(-2px);
        }

        .features {
            margin-top: 60px;
            display: flex;
            gap: 30px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .feature {
            flex: 1;
            min-width: 200px;
            padding: 20px;
            background: rgba(255,255,255,0.1);
            border-radius: 16px;
            backdrop-filter: blur(10px);
        }

        .feature-icon {
            font-size: 2rem;
            margin-bottom: 12px;
        }

        .feature h3 {
            font-size: 1.1rem;
            margin-bottom: 8px;
        }

        .feature p {
            font-size: 0.85rem;
            opacity: 0.8;
        }

        footer {
            margin-top: 60px;
            font-size: 0.8rem;
            opacity: 0.7;
        }
    </style>
</head>
<body>
    <div class="home-container">
        <div class="logo">🌙</div>
        <h1>Lunar Widgets</h1>
        <div class="tagline">Widgets astrológicos interativos</div>
        <div class="description">
            Conecte mães e bebês através da energia da Lua.<br>
            Widgets personalizados para sites, blogs e aplicações.
        </div>

        <div class="buttons">
            <a href="{{ route('login') }}" class="btn btn-primary">
                <x-lucide-lock class="lucid-icon" style="width: 20px; height: 20px;" /> Acessar painel
            </a>
            <a href="mailto:contato@lunarwidgets.com" class="btn btn-secondary">
                <x-lucide-mail class="lucid-icon" style="width: 20px; height: 20px;" /> Entre em contato</a>
        </div>

        <div class="features">
            <div class="feature">
                <div class="feature-icon"><x-lucide-moon class="lucid-icon" style="width: 40px; height: 40px;" /></div>
                <h3>Cálculo preciso</h3>
                <p>Baseado em efemérides astronômicas Swiss Ephemeris</p>
            </div>
            <div class="feature">
                <div class="feature-icon"><x-lucide-baby class="lucid-icon" style="width: 40px; height: 40px;" /></div>
                <h3>Para mães e bebês</h3>
                <p>Interpretações exclusivas para os primeiros anos</p>
            </div>
            <div class="feature">
                <div class="feature-icon"><x-lucide-laptop class="lucid-icon" style="width: 40px; height: 40px;" /></div>
                <h3>Widget embed</h3>
                <p>Instale em qualquer site com uma única linha de código</p>
            </div>
        </div>

        <footer>
            <p>© {{ date('Y') }} Lunar Widgets. Todos os direitos reservados.</p>
        </footer>
    </div>
</body>
</html>
