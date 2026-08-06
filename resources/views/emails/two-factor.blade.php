@extends('emails.layout')

@section('content')
    <h2 style="color: #f9fafb; margin-top: 0; font-size: 20px;">
        {{ $purpose === 'setup' ? 'Ativação de 2FA' : 'Verificação de Login' }}
    </h2>

    <p style="color: #9ca3af; line-height: 1.6; margin-bottom: 20px;">
        {{ $purpose === 'setup'
            ? 'Você solicitou a ativação da autenticação de dois fatores. Utilize o código abaixo para confirmar:'
            : 'Detectamos uma tentativa de acesso. Utilize o código abaixo para confirmar sua identidade:' }}
    </p>

    <div style="display: inline-block; background: #1f2937; border: 1px solid #374151; padding: 15px 30px; border-radius: 8px; font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #818cf8; margin: 10px 0;">
        {{ $code }}
    </div>

    <p style="color: #6b7280; font-size: 13px; margin-top: 20px;">
        Este código expira em 10 minutos.
    </p>
@endsection
