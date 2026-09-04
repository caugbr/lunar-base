@extends('emails.layout')

@section('content')
    <h2 style="color: #f9fafb; margin-top: 0; font-size: 20px;">
        Confirme seu endereço de e-mail
    </h2>

    <p style="color: #9ca3af; line-height: 1.6; margin-bottom: 25px;">
        Olá, <strong>{{ $user->name ?? 'Usuário' }}</strong>! Obrigado por se cadastrar no {{ setting('general.site_name', config('app.name')) }}. Para ativar a sua conta e ter acesso ao sistema, clique no botão abaixo:
    </p>

    <div style="margin: 30px 0;">
        <a href="{{ $url }}" style="background-color: #4f46e5; color: #ffffff; padding: 14px 28px; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px; display: inline-block;">
            Confirmar Meu E-mail
        </a>
    </div>

    <p style="color: #6b7280; font-size: 13px; margin-top: 25px; line-height: 1.5;">
        Se você não criou uma conta no {{ setting('general.site_name', config('app.name')) }}, nenhuma ação é necessária.
    </p>

    <hr style="border: none; border-top: 1px solid #1f2937; margin: 30px 0;">

    <p style="color: #6b7280; font-size: 12px; text-align: left; word-break: break-all;">
        Se você estiver tendo problemas para clicar no botão "Confirmar Meu E-mail", copie e cole a URL abaixo no seu navegador:<br>
        <a href="{{ $url }}" style="color: #818cf8; text-decoration: underline;">{{ $url }}</a>
    </p>
@endsection
