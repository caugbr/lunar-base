@extends('emails.layout')

@section('content')
    <h2 style="color: #f9fafb; margin-top: 0; font-size: 20px;">
        Redefinição de Senha
    </h2>

    <p style="color: #9ca3af; line-height: 1.6; margin-bottom: 20px;">
        Olá{{ $userName ? ', ' . $userName : '' }}! Recebemos uma solicitação para redefinir a senha de acesso da sua conta. Clique no botão abaixo para cadastrar uma nova senha:
    </p>

    <div style="margin: 28px 0; text-align: center;">
        <a href="{{ $url }}"
           style="display: inline-block; background-color: #4f46e5; color: #ffffff; padding: 12px 28px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 15px; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);"
           target="_blank">
            Redefinir Minha Senha
        </a>
    </div>

    <p style="color: #6b7280; font-size: 13px; line-height: 1.5; margin-top: 25px;">
        Este link expira em {{ config('auth.passwords.users.expire', 60) }} minutos.<br>
        Se você não fez essa solicitação, ignore este e-mail — sua senha atual permanecerá inalterada.
    </p>

    <div style="border-top: 1px solid #374151; margin-top: 25px; padding-top: 20px;">
        <p style="color: #6b7280; font-size: 12px; margin-bottom: 5px; word-break: break-all;">
            Caso o botão não funcione, copie e cole o endereço abaixo no seu navegador:
        </p>
        <a href="{{ $url }}" style="color: #818cf8; font-size: 12px; word-break: break-all; text-decoration: none;">
            {{ $url }}
        </a>
    </div>
@endsection
