@extends('auth.layout')

@section('title', 'Login')
@section('icon', 'lock')

{{-- Injeta a classe interim no body APENAS se a requisição pedir --}}
@if(request()->filled('interim'))
    @section('body_class', 'interim')
@endif

@push('styles')
<style>
    /* Estilos específicos do login */
    .login-container .password-strength-meter {
        display: none !important;
    }
    .auth-submit-btn {
        width: 100%;
        justify-content: center;
    }
</style>
@endpush

@section('content')
    @if(request()->filled('interim'))
        <div class="interim-notice" style="background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; padding: 12px; border-radius: 6px; margin-bottom: 16px;">
            <strong>Sua sessão expirou por inatividade.</strong><br>
            Faça login novamente e continue de onde parou.
        </div>
    @endif

    <x-form
        method="POST"
        :action="route('login')"
        :target="!request()->has('interim') ? '_top' : null"
    >
        @if(request()->filled('interim'))
            <input type="hidden" name="interim" value="1">
        @endif

        <x-form.text
            type="email"
            name="email"
            label="E-mail"
            autocomplete="username"
            :value="old('email')"
            required
            autofocus
        />

        <x-form.password
            name="password"
            label="Senha"
            autocomplete="current-password"
            required
        />

        @if(setting('auth.use_captcha'))
        @php
            $siteKey = setting('auth.turnstile_site_key', '');
            $appearance = setting('auth.turnstile_appearance', 'always');
            $size = setting('auth.turnstile_size', 'normal');
        @endphp
        <div class="captcha-wrapper">
            <div
                class="cf-turnstile"
                data-sitekey="{{ $siteKey }}"
                data-theme="light"
                data-size="{{ $size }}"
                data-appearance="{{ $appearance }}"
            ></div>
        </div>
        @endif

        <div style="text-align: right; margin-top: -8px; margin-bottom: 16px;">
            <a href="{{ route('password.request') }}" style="font-size: 0.85rem; color: var(--text-muted, #64748b); text-decoration: none;">
                Esqueceu a senha?
            </a>
        </div>

        <x-form.button type="submit" icon="log-in" class="auth-submit-btn">
            Entrar
        </x-form.button>
    </x-form>
@endsection

@section('footer')
    <div class="info">
        {{ config('site.loginFooterText', 'Acesso restrito') }}
    </div>
@endsection

@push('scripts')
    <script>
        window.addEventListener('pageshow', function (event) {
            if (event.persisted) {
                window.location.reload();
            }
        });
    </script>

    @if(setting('auth.use_captcha'))
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    @endif
@endpush
