@extends('auth.layout')

@section('title', 'Recuperar Senha')
@section('heading', 'Recuperar Senha')
@section('icon', 'key-round')

@section('content')
    <p style="font-size: 0.9rem; color: var(--text-muted, #64748b); margin-bottom: 20px;">
        Informe o e-mail da sua conta e enviaremos um link para você cadastrar uma nova senha.
    </p>

    <x-form method="POST" :action="route('password.email')">
        <x-form.text
            type="email"
            name="email"
            label="E-mail"
            autocomplete="username"
            :value="old('email')"
            required
            autofocus
        />

        <x-form.button type="submit" icon="send" class="auth-submit-btn">
            Enviar Link de Recuperação
        </x-form.button>
    </x-form>
@endsection

@section('footer')
    <div class="info" style="margin-top: 20px;">
        <a href="{{ route('login') }}" style="color: inherit; text-decoration: underline;">
            Voltar para o login
        </a>
    </div>
@endsection
