@extends('auth.layout')

@section('title', 'Criar Nova Senha')
@section('heading', 'Nova Senha')
@section('icon', 'lock')

@section('content')
    <x-form method="POST" :action="route('password.update')">
        <input type="hidden" name="token" value="{{ $token }}">

        <x-form.text
            type="email"
            name="email"
            label="E-mail"
            autocomplete="username"
            :value="old('email', $email)"
            required
            readonly
            style="opacity: 0.7; cursor: not-allowed;"
        />

        {{-- Gera automaticamente os campos de senha + confirmação,
             com botão de reveal integrado e barra de força com mínimo de 8 chars --}}
        <x-form.password
            name="password"
            label="Nova Senha"
            min-length="8"
            required
            autofocus
            confirm
        />

        <x-form.button type="submit" icon="key" class="auth-submit-btn" style="margin-top: 16px;">
            Salvar Nova Senha
        </x-form.button>
    </x-form>
@endsection
