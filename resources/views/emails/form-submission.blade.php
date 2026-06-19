<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova resposta: {{ $form->title }}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f4f4; font-family: Arial, sans-serif;">

    {{-- Container Principal --}}
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f4f4f4; padding: 20px;">
        <tr>
            <td align="center">
                {{-- Tabela do E-mail (Largura fixa para compatibilidade) --}}
                <table width="600" cellpadding="0" cellspacing="0" border="0" style="background-color: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;">

                    {{-- Cabeçalho (Padrão Lunar Apps) --}}
                    <tr>
                        <td style="padding: 20px; background-color: #1f2937; color: #ffffff; text-align: center;">
                            <h1 style="margin: 0; font-size: 24px; font-weight: bold;">Lunar Apps</h1>
                            <p style="margin: 5px 0 0; font-size: 14px; color: #9ca3af;">Componentes Astrológicos</p>
                        </td>
                    </tr>

                    {{-- Corpo do E-mail --}}
                    <tr>
                        <td style="padding: 30px;">
                            <h2 style="color: #1f2937; margin-top: 0; font-size: 20px;">
                                Nova resposta do formulário: {{ $form->slug }}!
                            </h2>

                            <p style="color: #4b5563; line-height: 1.6; margin-bottom: 20px;">
                                Você recebeu uma nova submissão através do site. Abaixo estão os dados enviados pelo usuário:
                            </p>

                            {{-- Tabela de Dados Dinâmicos --}}
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse: collapse; margin-bottom: 20px;">
                                @foreach($data as $key => $value)
                                    <tr>
                                        <td width="35%" style="padding: 10px; border-bottom: 1px solid #e5e7eb; background-color: #f9fafb; font-weight: bold; color: #374151; vertical-align: top;">
                                            {{ ucfirst(str_replace('_', ' ', $key)) }}
                                        </td>
                                        <td width="65%" style="padding: 10px; border-bottom: 1px solid #e5e7eb; color: #4b5563; vertical-align: top;">
                                            @if(is_array($value))
                                                {{ implode(', ', $value) }}
                                            @elseif(is_bool($value))
                                                {{ $value ? 'Sim' : 'Não' }}
                                            @else
                                                {{ $value }}
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </table>

                            {{-- Caixa de Nota de Segurança (Padrão Visual) --}}
                            <div style="background-color: #f3f4f6; border-left: 4px solid #3b82f6; padding: 15px; margin: 20px 0;">
                                <strong style="color: #1f2937; display: block; margin-bottom: 5px;">Nota de Segurança:</strong>
                                <p style="margin: 0; color: #4b5563; font-size: 14px; line-height: 1.5;">
                                    Esta é uma mensagem automática gerada pelo sistema. Os dados acima foram submetidos diretamente pelo usuário através do formulário público.
                                </p>
                            </div>
                        </td>
                    </tr>

                    {{-- Rodapé (Padrão Lunar Apps) --}}
                    <tr>
                        <td style="padding: 20px; background-color: #f9fafb; text-align: center; border-top: 1px solid #e5e7eb;">
                            <p style="margin: 0; font-size: 12px; color: #6b7280;">
                                Esta é uma notificação automática do sistema sobre os seus serviços. &copy; {{ date('Y') }} Lunar Apps
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>
