@php
    $typeOptions = [
        'info' => 'Informativo (Azul)',
        'warning' => 'Aviso / Alerta (Amarelo)',
        'error' => 'Crítico / Erro (Vermelho)',
        'success' => 'Sucesso (Verde)',
        'custom' => 'Personalizado...',
    ];

    $targetTypeOptions = [
        'all' => 'Todos os usuários',
        'roles' => 'Perfis específicos (Roles)',
        'users' => 'Usuários específicos',
    ];

    $roleOptions = collect(config('rolesPermissions.roles', []))->mapWithKeys(function ($roleData, $roleKey) {
        return [$roleKey => $roleData['name'] ?? ucfirst($roleKey)];
    })->all();

    $userOptions = \App\Models\User::orderBy('name')
        ->get()
        ->mapWithKeys(function ($user) {
            return [$user->id => "{$user->name} ({$user->email})"];
        })
        ->all();

    $currentRoles = ($notice->target_type === 'roles' ? $notice->target_values : []) ?? [];
    $currentUsers = ($notice->target_type === 'users' ? $notice->target_values : []) ?? [];
@endphp

<x-form :action="$action" :method="$method ?? 'POST'">
    <div class="form-row">
        <div class="form-field-full">
            <x-form.textarea
                name="message"
                label="Mensagem do aviso"
                :value="$notice->message"
                rows="4"
                required
                editable
                help="Esta mensagem será exibida na barra de alertas do painel. Este campo suporta HTML. Use os botões para inserir tags de formatação. Quebras de linha são preservadas."
            />
        </div>
    </div>

    <div class="form-row">
        <x-form.select
            name="type"
            id="type"
            label="Tipo de alerta"
            :options="$typeOptions"
            :value="$notice->type ?? 'info'"
            :placeholder="false"
            required
        />

        <x-form.text
            name="close_text"
            label="Texto de fechamento"
            :value="$notice->close_text ?? 'Ok, ciente'"
            help="Ao passar o mouse sobre o X de fechar, esse texto aparece. Padrão: Ok, ciente."
        />

        <x-form.date
            type="datetime-local"
            name="expires_at"
            label="Data de expiração (opcional)"
            :value="$notice->expires_at"
            help="Após esta data, o aviso deixará de ser exibido automaticamente."
        />
    </div>

    <div class="form-row customize-line" id="customize-wrapper">
        <x-form.color
            name="color"
            label="Cor personalizada"
            :value="$notice->color"
            help="Cor para a fonte e a borda da caixa de mensagem. A cor de fundo será a mesma cor a 10%."
        />

        <x-picker.icon
            name="icon"
            label="Ícone personalizado (opcional)"
            :value="$notice->icon"
            help="Se quiser trocar o ícone à esquerda, escolha aqui um outro ícone para a mensagem."
        />
    </div>

    <div class="form-row">
        <div class="form-field-full target-panel">
            <x-form.radio
                name="target_type"
                label="Destinatários"
                :options="$targetTypeOptions"
                :value="$notice->target_type ?? 'all'"
                inline
                required
            />
        </div>
    </div>

    <div class="form-row target-block" id="block-roles">
        <div class="form-field-full roles-panel">
            <x-form.checkbox
                name="target_roles"
                label="Selecione os perfis que verão este aviso:"
                :options="$roleOptions"
                :value="$currentRoles"
                inline
            />
        </div>
    </div>

    <div class="form-row target-block" id="block-users">
        <div class="form-field-full">
            <x-form.select
                name="target_users"
                id="target_users"
                label="Selecione os usuários:"
                :options="$userOptions"
                :value="$currentUsers"
                multiple
                class="users-multiselect"
                help="Segure Ctrl (ou Cmd no Mac) para selecionar múltiplos usuários."
            />
        </div>
    </div>

    <div class="form-row">
        <x-form.switch
            name="is_active"
            label="Este aviso está ativo?"
            :checked="$notice->exists ? (bool)$notice->is_active : true"
            active="Sim"
            inactive="Não"
            help="Se desligado, o aviso não será exibido para nenhum usuário."
        />
    </div>

    <div class="buttons">
        <x-form.button type="submit" variant="primary">
            <x-lucide-save class="lucid-icon" /> {{ $submitLabel ?? 'Salvar' }}
        </x-form.button>
    </div>
</x-form>

@push('styles')
<style>
    .form-field-full {
        flex: 1 1 100%;
        width: 100%;
    }

    .target-panel .radio-group,
    .roles-panel .checkbox-group {
        display: flex;
        gap: 1.5rem;
        flex-wrap: wrap;
        padding: 0.75rem;
        background-color: var(--color-bg-dark, #f8f9fa);
        border: 1px solid var(--color-border, #ced4da);
        border-radius: 8px;
        margin-top: 0.375rem;
    }

    .target-panel label,
    .roles-panel label {
        margin-bottom: 0 !important;
    }

    .users-multiselect {
        min-height: 140px;
        width: 100%;
        padding: 0.5rem;
        border-radius: 6px;
        background-color: var(--color-bg-dark, #ffffff);
        border: 1px solid var(--color-border, #ced4da);
        color: var(--color-text, #212529);
    }

    /* Controle de visibilidade imune a sobreposicao de flex externo */
    .form-row.target-block:not(.is-visible),
    .form-row.customize-line:not(.is-visible) {
        display: none !important;
    }

    .form-row.target-block.is-visible,
    .form-row.customize-line.is-visible {
        display: flex !important;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const typeSelect = document.getElementById('type');
    const customWrapper = document.getElementById('customize-wrapper');
    const targetRadios = document.querySelectorAll('input[name="target_type"]');
    const blockRoles = document.getElementById('block-roles');
    const blockUsers = document.getElementById('block-users');

    const updateTypeVisibility = () => {
        if (!typeSelect || !customWrapper) return;
        customWrapper.classList.toggle('is-visible', typeSelect.value === 'custom');
    };

    const updateTargetVisibility = () => {
        const selected = document.querySelector('input[name="target_type"]:checked')?.value;
        if (blockRoles) {
            blockRoles.classList.toggle('is-visible', selected === 'roles');
        }
        if (blockUsers) {
            blockUsers.classList.toggle('is-visible', selected === 'users');
        }
    };

    if (typeSelect) {
        typeSelect.addEventListener('change', updateTypeVisibility);
        updateTypeVisibility();
    }

    targetRadios.forEach(radio => radio.addEventListener('change', updateTargetVisibility));
    updateTargetVisibility();
});
</script>
@endpush
