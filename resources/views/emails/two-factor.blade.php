<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Lunar Apps</title>
</head>
<body style="font-family: 'Inter', 'Segoe UI', Helvetica, Arial, sans-serif; background-color: #0b0f19; margin: 0; padding: 40px 20px; -webkit-font-smoothing: antialiased;">

    <table align="center" border="0" cellpadding="0" cellspacing="0" width="600" style="background-color: #111827; border: 1px solid #1f2937; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);">

        <tr>
            <td style="background: linear-gradient(135deg, #4f46e5 0%, #1e1b4b 100%); background-color: #4f46e5; padding: 45px 40px; text-align: center;">
                <h1 style="color: #ffffff; margin: 0; font-size: 28px; font-weight: 800; letter-spacing: -0.5px;">
                    {{ setting('general.site_name') }}
                </h1>
                <p style="color: #c7d2fe; margin: 5px 0 0 0; font-size: 14px; letter-spacing: 1px; text-transform: uppercase;">Componentes Astrológicos</p>
            </td>
        </tr>

        <tr>
            <td style="padding: 45px 40px; color: #e5e7eb; line-height: 1.75; font-size: 16px; text-align: center;">
                <h2 style="color: #1f2937; margin-top: 0; font-size: 20px;">
                    {{ $purpose === 'setup' ? 'Ativação de 2FA' : 'Verificação de Login' }}
                </h2>

                <p style="color: #4b5563; line-height: 1.6; margin-bottom: 20px;">
                    {{ $purpose === 'setup'
                        ? 'Você solicitou a ativação da autenticação de dois fatores. Utilize o código abaixo para confirmar:'
                        : 'Detectamos uma tentativa de acesso. Utilize o código abaixo para confirmar sua identidade:' }}
                </p>

                <div style="display: inline-block; background: #f3f4f6; padding: 15px 30px; border-radius: 8px; font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #1f2937; margin: 10px 0;">
                    {{ $code }}
                </div>

                <p style="color: #6b7280; font-size: 12px; margin-top: 20px;">
                    Este código expira em 10 minutos.
                </p>
            </td>
        </tr>

        <tr>
            <td style="background-color: #0b0f19; padding: 30px 40px; text-align: center; font-size: 13px; color: #6b7280; border-top: 1px solid #1f2937;">
                Esta é uma notificação automática do sistema sobre os seus serviços.<br>
                <span style="color: #4b5563; display: inline-block; margin-top: 10px;">© {{ date('Y') }} Lunar Apps</span>
            </td>
        </tr>
    </table>

</body>
</html>
