@extends('admin.layout')

@section('header_title', 'Novo Aviso')
@section('header_subtitle', 'Crie um alerta persistente para os usuários do painel')

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2><x-lucide-bell-plus class="lucid-icon" /> Novo Aviso</h2>
        <a href="{{ route('admin.notices.index') }}" class="admin-btn admin-btn-secondary">
            <x-lucide-arrow-left class="lucid-icon" /> Voltar
        </a>
    </div>

    <form method="POST" action="{{ route('admin.notices.store') }}">
        @csrf

        <div class="admin-form-row">
            <div class="form-group form-group-full">
                <label for="message">Mensagem do Aviso *</label>
                <x-html-editor name="message" value="{{ old('message') }}" />
                <small>Esta mensagem será exibida na barra de alertas do painel.</small>
                @error('message') <small class="error">{{ $message }}</small> @enderror
            </div>
        </div>

        <div class="admin-form-row">
            <div class="form-group">
                <label for="type">Tipo de Alerta *</label>
                <select name="type" id="type" required>
                    <option value="info" {{ old('type') === 'info' ? 'selected' : '' }}>Informativo (Azul)</option>
                    <option value="warning" {{ old('type') === 'warning' ? 'selected' : '' }}>Aviso / Alerta (Amarelo)</option>
                    <option value="error" {{ old('type') === 'error' ? 'selected' : '' }}>Crítico / Erro (Vermelho)</option>
                    <option value="success" {{ old('type') === 'success' ? 'selected' : '' }}>Sucesso (Verde)</option>
                    <option value="custom" {{ old('type') === 'custom' ? 'selected' : '' }}>Personalizado...</option>
                </select>
                @error('type') <small class="error">{{ $message }}</small> @enderror
            </div>

            <div class="form-group">
                <label for="expires_at">Data de Expiração (Opcional)</label>
                <input type="datetime-local" name="expires_at" id="expires_at" value="{{ old('expires_at') }}">
                <small>Após esta data, o aviso deixará de ser exibido automaticamente.</small>
                @error('expires_at') <small class="error">{{ $message }}</small> @enderror
            </div>
        </div>

        <div class="admin-form-row customize-line" style="display: none;">
            <div class="form-group">
                <label for="color">Cor personalizada</label>
                <x-input-color name="color" value="{{ old('color') }}" />
                <small>Cor para a fonte e a borda da caixa de mensagem. A cor de fundo será a mesma cor a 10%.</small>
            </div>
            <div class="form-group">
                <label for="icon">Ícone personalizado (Opcional)</label>
                <x-icon-selector
                    name="icon"
                    id="icon"
                    value="{{ old('icon') }}"
                    can_clear="{{ true }}"
                />
                <small>Se quiser trocar o ícone à esquerda, escolha aqui um outro ícone para a mensagem.</small>
                @error('icon') <small class="error">{{ $message }}</small> @enderror
            </div>
        </div>

        <div class="admin-form-row">
            <div class="form-group form-group-full">
                <label>Destinatários *</label>
                <div class="target-options">
                    <label class="radio-label">
                        <input type="radio" name="target_type" value="all" {{ old('target_type', 'all') === 'all' ? 'checked' : '' }}>
                        <span>Todos os usuários</span>
                    </label>
                    <label class="radio-label">
                        <input type="radio" name="target_type" value="roles" {{ old('target_type') === 'roles' ? 'checked' : '' }}>
                        <span>Perfis Específicos (Roles)</span>
                    </label>
                    <label class="radio-label">
                        <input type="radio" name="target_type" value="users" {{ old('target_type') === 'users' ? 'checked' : '' }}>
                        <span>Usuários Específicos</span>
                    </label>
                </div>
            </div>
        </div>

        {{-- Bloco condicional: Seleção de Roles --}}
        <div class="admin-form-row target-block" id="block-roles">
            <div class="form-group form-group-full">
                <label>Selecione as Roles que verão este aviso:</label>
                <div class="roles-checklist">
                    @foreach(config('rolesPermissions.roles', []) as $roleKey => $roleData)
                        <label class="checkbox-box">
                            <input type="checkbox" name="target_roles[]" value="{{ $roleKey }}" {{ is_array(old('target_roles')) && in_array($roleKey, old('target_roles')) ? 'checked' : '' }}>
                            <span>{{ $roleData['name'] ?? ucfirst($roleKey) }}</span>
                        </label>
                    @endforeach
                </div>
                @error('target_roles') <small class="error">{{ $message }}</small> @enderror
            </div>
        </div>

        {{-- Bloco condicional: Seleção de Usuários --}}
        <div class="admin-form-row target-block" id="block-users">
            <div class="form-group form-group-full">
                <label for="target_users">Selecione os Usuários:</label>
                <select name="target_users[]" id="target_users" multiple class="users-multiselect">
                    @foreach(\App\Models\User::orderBy('name')->get() as $user)
                        <option value="{{ $user->id }}" {{ is_array(old('target_users')) && in_array($user->id, old('target_users')) ? 'selected' : '' }}>
                            {{ $user->name }} ({{ $user->email }})
                        </option>
                    @endforeach
                </select>
                <small>Segure Ctrl (ou Cmd no Mac) para selecionar múltiplos usuários.</small>
                @error('target_users') <small class="error">{{ $message }}</small> @enderror
            </div>
        </div>

        <div class="admin-form-row">
            <div class="form-group">
                <label for="is_active">Este aviso está ativo?</label>
                <x-switch name="is_active" id="is_active" :checked="old('is_active', true)" active="Sim" inactive="Não" />
                <small>Se desligado, o aviso não será exibido para nenhum usuário.</small>
            </div>
        </div>

        <div class="buttons">
            <button type="submit" class="admin-btn admin-btn-primary">
                <x-lucide-save class="lucid-icon" /> Publicar Aviso
            </button>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
    .form-group-full {
        flex: 1 1 100%;
    }
    .target-options {
        display: flex;
        gap: 1.5rem;
        padding: 0.75rem;
        background-color: var(--color-bg-dark);
        border: 1px solid var(--color-border);
        border-radius: 8px;
    }
    .target-options label,
    .roles-checklist label {
        margin: 0;
    }
    .radio-label {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
    }
    .target-block {
        display: none;
    }
    .roles-checklist {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        padding: 0.75rem;
        background-color: var(--color-bg-dark);
        border: 1px solid var(--color-border);
        border-radius: 8px;
    }
    .checkbox-box {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        cursor: pointer;
    }
    .users-multiselect {
        min-height: 140px;
        width: 100%;
        padding: 0.5rem;
        border-radius: 6px;
        background-color: var(--color-bg-dark);
        border: 1px solid var(--color-border);
        color: var(--color-text);
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const radios = document.querySelectorAll('input[name="target_type"]');
        const blockRoles = document.getElementById('block-roles');
        const blockUsers = document.getElementById('block-users');

        function updateTargetVisibility() {
            const selected = document.querySelector('input[name="target_type"]:checked')?.value;

            blockRoles.style.display = selected === 'roles' ? 'flex' : 'none';
            blockUsers.style.display = selected === 'users' ? 'flex' : 'none';
        }

        radios.forEach(radio => radio.addEventListener('change', updateTargetVisibility));
        updateTargetVisibility();

        const types = document.getElementById('type');
        const customLine = document.querySelector('.customize-line');

        types.addEventListener('change', () => {
            customLine.style.display = types.value === 'custom' ? 'flex' : 'none';
        });

        types.dispatchEvent(new Event('change'));
    });
</script>
@endpush
