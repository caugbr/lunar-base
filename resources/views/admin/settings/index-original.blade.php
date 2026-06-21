@extends('admin.layout')

@section('header_title', 'Configurações')
@section('header_subtitle', 'Configure o sistema')

@section('content')
<div class="admin-card">
    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
        @csrf

        @php
            $useTabs = setting('navigation.settings_in_tabs', false);
            $groupKeys = array_keys($groups);
            $activeTab = old('_active_tab', $groupKeys[0] ?? '');
        @endphp

        @if($useTabs)
            {{-- Navegação por Tabs --}}
            <div class="settings-tabs">
                @foreach($groups as $groupKey => $group)
                    <button type="button"
                        class="settings-tab {{ $loop->first ? 'active' : '' }}"
                        data-tab="{{ $groupKey }}"
                        onclick="switchTab('{{ $groupKey }}')">
                        @if(isset($group['icon']))
                            <x-dynamic-component component="lucide-{{ $group['icon'] }}" class="lucid-icon" />
                        @endif
                        {{ $group['tab'] ?? $group['title'] }}
                    </button>
                @endforeach
            </div>
            <input type="hidden" name="_active_tab" id="_active_tab" value="{{ $activeTab }}">
        @endif

        {{-- Loop sobre os GRUPOS (nível 1) --}}
        @foreach($groups as $groupKey => $group)
            <div class="settings-group {{ $useTabs ? 'tab-panel' : '' }} {{ $loop->first || !$useTabs ? 'active' : '' }}"
                 data-panel="{{ $groupKey }}">

                {{-- Cabeçalho do Grupo --}}
                <div class="group-header">
                    <h3 class="group-title">
                        @if(isset($group['icon']))
                            <x-dynamic-component component="lucide-{{ $group['icon'] }}" class="lucid-icon" />
                        @endif
                        {{ $group['title'] }}
                    </h3>
                    @if(isset($group['description']))
                        <p class="group-description">{{ $group['description'] }}</p>
                    @endif
                </div>

                {{-- Loop sobre os CAMPOS do grupo (nível 2) --}}
                @foreach($group['fields'] as $def)
                    @php
                        $value = old($def['key'], $def['value'] ?? $def['default'] ?? '');
                        $options = $def['options'] ?? [];
                        $attributes = $def['attributes'] ?? [];
                    @endphp

                    <div class="form-group">
                        <label for="{{ $def['key'] }}" class="field-label">
                            {{ $def['label'] }}
                        </label>

                        @switch($def['type'])
                            @case('textarea')
                                <textarea name="{{ $def['key'] }}" id="{{ $def['key'] }}" rows="3" class="form-input">{{ $value }}</textarea>
                                @break

                            @case('select')
                                <select name="{{ $def['key'] }}" id="{{ $def['key'] }}" class="form-input">
                                    @foreach($options as $optValue => $optLabel)
                                        <option value="{{ $optValue }}" {{ $value == $optValue ? 'selected' : '' }}>
                                            {{ $optLabel }}
                                        </option>
                                    @endforeach
                                </select>
                                @break

                            @case('radio')
                                <div class="radio-group">
                                    @foreach($options as $optValue => $optLabel)
                                        <label class="radio-label">
                                            <input type="radio" name="{{ $def['key'] }}" value="{{ $optValue }}" {{ $value == $optValue ? 'checked' : '' }}>
                                            <span>{{ $optLabel }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                @break

                            @case('checkbox')
                                @if(!empty($options))
                                    @php
                                        $currentValues = is_array($value) ? $value : ($value ? explode(',', $value) : []);
                                    @endphp
                                    <div class="checkbox-group">
                                        @foreach($options as $optValue => $optLabel)
                                            <label class="checkbox-label">
                                                <input type="checkbox"
                                                    name="{{ $def['key'] }}[]"
                                                    value="{{ $optValue }}"
                                                    {{ in_array($optValue, $currentValues) ? 'checked' : '' }}>
                                                <span>{{ $optLabel }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    <input type="hidden" name="{{ $def['key'] }}_is_array" value="1">
                                @else
                                    <label class="checkbox-label">
                                        <input type="hidden" name="{{ $def['key'] }}" value="0">
                                        <input type="checkbox" name="{{ $def['key'] }}" value="1" {{ $value ? 'checked' : '' }}>
                                        <span>Sim</span>
                                    </label>
                                @endif
                                @break

                            @case('switch')
                                <x-switch
                                    name="{{ $def['key'] }}"
                                    active="{{ $def['active'] ?? 'Ativado' }}"
                                    inactive="{{ $def['inactive'] ?? 'Desativado' }}"
                                    checked="{{ $value }}"
                                />
                                @break

                            @case('number')
                                <input type="number"
                                    name="{{ $def['key'] }}"
                                    id="{{ $def['key'] }}"
                                    value="{{ $value }}"
                                    @foreach($attributes as $attr => $attrValue)
                                        {{ $attr }}="{{ $attrValue }}"
                                    @endforeach
                                    class="form-input form-input-narrow">
                                @break

                            @case('image')
                                <div class="image-type">
                                    @if($value)
                                        <div class="image-preview">
                                            <img src="{{ $value }}" alt="{{ $def['label'] }}">
                                        </div>
                                    @endif

                                    <div class="image-input">
                                        <x-upload-area name="{{ $def['key'] }}" />

                                        @if($value)
                                            <label class="remove-image-label">
                                                <input type="checkbox"
                                                    name="remove_settings[{{ $def['key'] }}]"
                                                    value="1">
                                                Remover imagem
                                            </label>
                                        @endif

                                        <input type="hidden"
                                            name="{{ $def['key'] }}_current"
                                            value="{{ $def['path'] ?? $value }}">
                                    </div>
                                </div>
                                <small class="image-help">
                                    @if($value)
                                        Marque "Remover imagem" para apagar, ou selecione um novo arquivo para substituir.
                                    @else
                                        Selecione uma imagem para enviar.
                                    @endif
                                </small>
                                @break

                            @case('url')
                                <input type="url" name="{{ $def['key'] }}" id="{{ $def['key'] }}" value="{{ $value }}" class="form-input" placeholder="https://...">
                                @break

                            @case('email')
                                <input type="email" name="{{ $def['key'] }}" id="{{ $def['key'] }}" value="{{ $value }}" class="form-input" placeholder="email@exemplo.com">
                                @break

                            @case('password')
                                <input type="password" name="{{ $def['key'] }}" id="{{ $def['key'] }}" value="" class="form-input" placeholder="••••••••">
                                @if($value)
                                    <small class="form-help">Senha configurada. Deixe em branco para manter.</small>
                                @endif
                                @break

                            @default
                                <input type="text" name="{{ $def['key'] }}" id="{{ $def['key'] }}" value="{{ $value }}" class="form-input">
                        @endswitch

                        @if(!empty($def['description']))
                            <small class="form-help">{{ $def['description'] }}</small>
                        @endif

                        @error($def['key'])
                            <small class="error">{{ $message }}</small>
                        @enderror
                    </div>
                @endforeach
            </div>
        @endforeach

        <div class="buttons">
            <button type="submit" class="admin-btn admin-btn-primary">
                <x-lucide-save class="lucid-icon" /> Salvar Configurações
            </button>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
    /* ===== Tabs ===== */
    .settings-tabs {
        display: flex;
        gap: 4px;
        margin-bottom: 24px;
        border-bottom: 2px solid #e5e7eb;
        overflow-x: auto;
        padding-bottom: 2px;
    }

    .settings-tab {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 10px 16px;
        border: none;
        background: transparent;
        color: #6b7280;
        font-size: 0.9rem;
        font-weight: 500;
        cursor: pointer;
        white-space: nowrap;
        border-bottom: 2px solid transparent;
        margin-bottom: -2px;
        transition: color 0.2s, border-color 0.2s;
    }

    .settings-tab:hover {
        color: #374151;
    }

    .settings-tab.active {
        color: #3b82f6;
        border-bottom-color: #3b82f6;
    }

    .settings-tab .lucid-icon {
        width: 16px;
        height: 16px;
    }

    /* ===== Tab Panels ===== */
    .tab-panel {
        display: none;
    }

    .tab-panel.active {
        display: block;
        animation: fadeIn 0.2s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(4px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* ===== Grupos de Configuração ===== */
    .settings-group {
        border-bottom: 1px solid #e5e7eb;
        padding-bottom: 1.5rem;
        margin-bottom: 2.5rem;
    }
    .settings-group.tab-panel,
    .settings-group:last-child {
        border-bottom: none;
        padding-bottom: 0;
        margin-bottom: 0;
    }

    .group-header {
        margin-bottom: 1.5rem;
    }

    .group-title {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 1.25rem;
        font-weight: 600;
        color: #1f2937;
    }

    .group-description {
        font-size: 0.875rem;
        color: #6b7280;
        margin: 0.25rem 0 0;
    }

    /* ===== Formulário ===== */
    .form-group {
        margin-bottom: 1rem;
    }

    .field-label {
        display: block;
        font-weight: 500;
        margin-bottom: 0.25rem;
        color: #374151;
    }

    .form-input {
        /* suas regras existentes de form-input */
    }

    .form-input-narrow {
        width: 150px;
    }

    .form-help {
        display: block;
        font-size: 0.75rem;
        color: #6b7280;
        margin-top: 0.25rem;
    }

    .error {
        color: #ef4444;
        display: block;
    }

    /* ===== Radio & Checkbox ===== */
    .radio-group,
    .checkbox-group {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .radio-label,
    .checkbox-label {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        margin-right: 1rem;
        cursor: pointer;
    }

    /* ===== Switch ===== */
    .switch-label {
        display: inline-flex !important;
        align-items: center;
        gap: 0.75rem;
        cursor: pointer;
        position: relative;
    }

    .switch-input {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
    }

    .switch-track {
        position: relative;
        display: inline-block;
        width: 44px;
        height: 24px;
        background-color: #d1d5db;
        border-radius: 9999px;
        transition: background-color 0.2s ease;
        flex-shrink: 0;
    }

    .switch-thumb {
        position: absolute;
        top: 2px;
        left: 2px;
        width: 20px;
        height: 20px;
        background-color: #ffffff;
        border-radius: 50%;
        transition: transform 0.2s ease;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15);
    }

    .switch-text {
        font-size: 0.875rem;
        color: #7b96c1;
        user-select: none;
    }

    .switch-text::before {
        content: attr(data-inactive);
    }

    /* Estados */
    .switch-input:checked + .switch-track {
        background-color: #3b82f6;
    }

    .switch-input:checked + .switch-track .switch-thumb {
        transform: translateX(20px);
    }

    .switch-input:checked ~ .switch-text::before {
        content: attr(data-active);
    }

    .switch-input:focus + .switch-track {
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
    }

    /* ===== Imagem ===== */
    .image-type {
        display: flex;
        flex-direction: row;
        gap: 1rem;
        align-items: flex-start;
    }

    .image-preview img {
        max-width: 130px;
        border-radius: 0.375rem;
        border: 1px solid #e5e7eb;
    }

    .image-input {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        flex-grow: 1;
    }

    .remove-image-label {
        font-size: 0.875rem;
        color: #ef4444;
        cursor: pointer;
    }

    .remove-image-label input {
        margin-right: 0.25rem;
    }

    .image-help {
        color: #6b7280;
        display: block;
        margin-top: 0.5rem;
    }
</style>
@endpush

@push('scripts')
<script>
function switchTab(tabKey) {
    // Atualiza botões
    document.querySelectorAll('.settings-tab').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.tab === tabKey);
    });

    // Atualiza painéis
    document.querySelectorAll('.tab-panel').forEach(panel => {
        panel.classList.toggle('active', panel.dataset.panel === tabKey);
    });

    // Atualiza hidden input para persistir aba ativa no submit
    const hidden = document.getElementById('_active_tab');
    if (hidden) hidden.value = tabKey;
}

// Restaura aba ativa após erro de validação (se houver _active_tab no old input)
document.addEventListener('DOMContentLoaded', function() {
    const activeTab = document.getElementById('_active_tab')?.value;
    if (activeTab) {
        switchTab(activeTab);
    }
});
</script>
@endpush
