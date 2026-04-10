@extends('admin.layout')

@section('header_title', 'Editar Usuário')
@section('header_subtitle', $user->name)

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2><x-lucide-user class="lucid-icon" /> Editar Usuário</h2>
        <a href="{{ route('admin.users.index') }}" class="admin-btn admin-btn-secondary">
            <x-lucide-arrow-left class="lucid-icon" /> <span>Voltar</span>
        </a>
    </div>

    <form method="POST" action="{{ route('admin.users.update', $user->id) }}">
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

        <div class="admin-form-row">
            <div class="form-group">
                <label for="password">Nova senha</label>
                <input type="password" name="password" id="password" autocomplete="new-password">
                <small>Deixe em branco para manter a senha atual</small>
            </div>
            <div class="form-group">
                <label for="role_id">Perfil *</label>
                <select name="role_id" id="role_id" required>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}" {{ $user->role_id == $role->id ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <button type="submit" class="admin-btn admin-btn-primary">
            <x-lucide-save class="lucid-icon" /> Atualizar
        </button>
    </form>
</div>
@endsection
