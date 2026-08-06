<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ setting('general.site_name', config('app.name')) }}</title>
</head>
<body style="font-family: 'Inter', 'Segoe UI', Helvetica, Arial, sans-serif; background-color: #0b0f19; margin: 0; padding: 40px 20px; -webkit-font-smoothing: antialiased;">

    <table align="center" border="0" cellpadding="0" cellspacing="0" width="600" style="background-color: #111827; border: 1px solid #1f2937; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);">

        <!-- CABEÇALHO -->
        <tr>
            <td style="background: linear-gradient(135deg, #4f46e5 0%, #1e1b4b 100%); background-color: #4f46e5; padding: 45px 40px; text-align: center;">
                <h1 style="color: #ffffff; margin: 0; font-size: 28px; font-weight: 800; letter-spacing: -0.5px;">
                    {{ setting('general.site_name', config('app.name')) }}
                </h1>
                @if(setting('general.site_description'))
                <p style="color: #c7d2fe; margin: 5px 0 0 0; font-size: 14px; letter-spacing: 1px; text-transform: uppercase;">
                    {{ setting('general.site_description') }}
                </p>
                @endif
            </td>
        </tr>

        <!-- CONTEÚDO DINÂMICO -->
        <tr>
            <td style="padding: 45px 40px; color: #e5e7eb; line-height: 1.75; font-size: 16px; text-align: center;">
                @yield('content')
            </td>
        </tr>

        <!-- RODAPÉ -->
        <tr>
            <td style="background-color: #0b0f19; padding: 30px 40px; text-align: center; font-size: 13px; color: #6b7280; border-top: 1px solid #1f2937;">
                Esta é uma notificação automática do sistema sobre os seus serviços.<br>
                <span style="color: #4b5563; display: inline-block; margin-top: 10px;">© {{ date('Y') }} {{ setting('general.site_name', config('app.name')) }}</span>
            </td>
        </tr>
    </table>

</body>
</html>
