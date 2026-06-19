@extends('admin.layout')

@section('header_title', 'Novo Usuário')
@section('header_subtitle', 'Adicione um novo administrador ou editor')

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2><x-lucide-user class="lucid-icon" /> Novo Usuário</h2>
        <a href="{{ route('admin.users.index') }}" class="admin-btn admin-btn-secondary">
            <x-lucide-arrow-left class="lucid-icon" /> <span>Voltar</span>
        </a>
    </div>

    <form method="POST" action="{{ route('admin.users.store') }}">
        @csrf

        <div class="admin-form-row">
            <div class="form-group">
                <label for="name">Nome *</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required>
            </div>
            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required>
            </div>
        </div>

        <div class="admin-form-row">
            <div class="form-group">
                <x-password-field name="password" label="Senha *" required="true" />
            </div>
            <div class="form-group">
                <label for="role">Perfil *</label>
                <select name="role" id="role" required>
                    @foreach($roles as $role_id => $role)
                    <option value="{{ $role_id }}">{{ $role['name'] }}</option>
                    @endforeach
                    {{-- <option value="admin">Administrador</option>
                    <option value="editor">Editor</option> --}}
                </select>
            </div>
        </div>

        <div class="buttons">
            <button type="submit" class="admin-btn admin-btn-primary">
                <x-lucide-save class="lucid-icon" /> Salvar
            </button>
        </div>
    </form>
</div>
@endsection
