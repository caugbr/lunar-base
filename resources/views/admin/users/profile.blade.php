@extends('admin.layout')

@section('header_title', 'Meu Perfil')
@section('header_subtitle', 'Atualize seus dados e senha')

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2><x-lucide-user class="lucid-icon" /> Meus Dados</h2>
    </div>

    <form method="POST" action="{{ route('admin.profile.update') }}" id="profile_form">
        @csrf
        @method('PUT')

        <div class="admin-form-row">
            <div class="form-group">
                <label for="name">Nome *</label>
                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required>
            </div>

            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required>
            </div>
        </div>

        <hr style="margin: 24px 0;">

        <h3><x-lucide-lock class="lucid-icon" /> Alterar Senha</h3>

        <div class="admin-form-row">
            <x-password-field name="new_password" label="Nova senha *" confirm="true" required="true" />
        </div>

        <div class="buttons">
            <button type="submit" class="admin-btn admin-btn-primary">
                <x-lucide-save class="lucid-icon" /> Salvar alterações
            </button>
        </div>
    </form>
</div>

<x-lost-changes-warn selector="#profile_form" />

@if(setting('auth.2fa_enabled', false))
    @include('auth.partials.two-factor')
@endif

@endsection
