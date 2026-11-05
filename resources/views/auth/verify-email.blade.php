@extends('auth.layout')

@section('title', 'Verificação de E-mail')
@section('icon', 'mail')

@section('content')
    @if (session('status') == 'verification-link-sent' || session('success'))
        <div class="success-message" style="color: #10b981; background-color: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.25); padding: 0.75rem 1rem; border-radius: 6px; font-size: 0.875rem; margin-bottom: 1.25rem; text-align: center;">
            Um novo link de verificação foi enviado para o seu e-mail!
        </div>
    @endif

    <p style="font-size: 0.875rem; line-height: 1.6; margin-bottom: 1.5rem; text-align: center; color: var(--color-text-muted, #6B6880);">
        Obrigado por se cadastrar! Antes de acessar o sistema, por favor confirme seu e-mail clicando no link que enviamos para a sua caixa de entrada.
    </p>

    {{-- Reenviar com prevenção de duplo submit --}}
    <x-form method="POST" :action="route('verification.send')">
        <x-form.button type="submit" icon="send" class="auth-submit-btn">
            Reenviar E-mail de Verificação
        </x-form.button>
    </x-form>

    {{-- Botão de logout simples --}}
    <form method="POST" action="{{ route('logout') }}" style="margin-top: 1rem; text-align: center;">
        @csrf
        <button type="submit" style="background: none; border: none; color: var(--color-text-muted, #6B6880); font-size: 0.8rem; cursor: pointer; text-decoration: underline; padding: 0;">
            Sair da conta
        </button>
    </form>
@endsection

@section('footer')
    <div class="info">
        Verificação de e-mail obrigatória
    </div>
@endsection
