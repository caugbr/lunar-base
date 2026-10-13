@php
    $user = $user ?? new \App\Models\User();
    $isEdit = $user->exists;
    $roles = $roles ?? config('rolesPermissions.roles', []);
@endphp

<form method="POST" action="{{ $action }}" id="user_form">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="admin-form-row">
        <div class="form-group">
            <label for="name">Nome *</label>
            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required>
            @error('name') <small class="error">{{ $message }}</small> @enderror
        </div>
        <div class="form-group">
            <label for="email">Email *</label>
            <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required>
            @error('email') <small class="error">{{ $message }}</small> @enderror
        </div>
    </div>

    <div class="admin-form-row">
        <div class="form-group">
            <x-password-field
                name="password"
                :label="$isEdit ? 'Nova senha (opcional)' : 'Senha *'"
                :required="!$isEdit"
                autocomplete="new-password"
            />
            @if($isEdit)
                <small>Deixe em branco para manter a senha atual</small>
            @endif
            @error('password') <small class="error">{{ $message }}</small> @enderror
        </div>
        <div class="form-group">
            <label for="role">Perfil *</label>
            <select name="role" id="role" required>
                @foreach($roles as $role_id => $roleData)
                    <option value="{{ $role_id }}" {{ old('role', $user->role) === $role_id ? 'selected' : '' }}>
                        {{ $roleData['name'] ?? ucfirst($role_id) }}
                    </option>
                @endforeach
            </select>
            @error('role') <small class="error">{{ $message }}</small> @enderror
        </div>
    </div>

    <div class="buttons">
        <button type="submit" class="admin-btn admin-btn-primary">
            <x-lucide-save class="lucid-icon" /> {{ $isEdit ? 'Atualizar' : 'Salvar' }}
        </button>
    </div>
</form>

@if($isEdit)
    <x-lost-changes-warn selector="#user_form" />
@endif
