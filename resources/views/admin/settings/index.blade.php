@extends('admin.layout')

@section('header_title', 'Configurações')
@section('header_subtitle', 'Configure o sistema')

@section('content')
<div class="admin-card">
    <x-form
        action="{{ route('admin.settings.update') }}"
        method="POST"
        has-files
        id="settings_form"
        warn-changes
    >
        @php
            $groupKeys = array_keys($groups);
            $activeTab = old('_active_tab', $groupKeys[0] ?? '');
        @endphp

        {{-- Navegação por abas --}}
        <div class="settings-tabs">
            @foreach($groups as $groupKey => $group)
                <button type="button"
                    class="settings-tab {{ $loop->first ? 'active' : '' }}"
                    data-tab="{{ $groupKey }}"
                    disabled
                    onclick="switchTab('{{ $groupKey }}')">
                    @if(isset($group['icon']))
                        <x-dynamic-component component="lucide-{{ $group['icon'] }}" class="lucid-icon" />
                    @endif
                    {{ $group['tab'] ?? $group['title'] }}
                </button>
            @endforeach
        </div>
        <input type="hidden" name="_active_tab" id="_active_tab" value="{{ $activeTab }}">

        {{-- Loop sobre os grupos (nível 1) --}}
        @foreach($groups as $groupKey => $group)
            <div class="settings-group tab-panel {{ $loop->first ? 'active' : '' }}"
                 data-panel="{{ $groupKey }}">

                {{-- Cabeçalho do grupo --}}
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

                {{-- Loop sobre os campos do grupo (nível 2) --}}
                @foreach($group['fields'] as $def)

                    @if($def['type'] === 'subtitle')
                        <h3 class="group-title group-subtitle">
                            @if(isset($def['icon']))
                                <x-dynamic-component component="{{ 'lucide-' . $def['icon'] }}" class="lucid-icon" />
                            @endif
                            {{ $def['label'] }}
                        </h3>
                        @continue
                    @endif

                    @if($def['type'] === 'paragraph')
                        <p class="group-paragraph group-description">
                            {{ $def['text'] }}
                        </p>
                        @continue
                    @endif

                    @php
                        $value = old($def['key'], $def['value'] ?? $def['default'] ?? '');
                        $hasDependency = isset($def['depends_on']);
                    @endphp

                    <div class="form-group"
                        @if($hasDependency)
                            data-depends-on="{{ json_encode($def['depends_on']) }}"
                        @endif
                        @if(isset($def['warn_on_change']))
                            data-warn-on-change="{{ $def['warn_on_change'] }}"
                        @endif
                    >
                        @if($def['type'] === 'repeater')
                            @if($def['label'] ?? false)
                                <label class="form-label">{{ $def['label'] }}</label>
                            @endif
                            @include('admin.settings.partials.repeater', [
                                'def' => $def,
                                'value' => $value
                            ])
                        @else
                            @include('admin.settings.partials.input', [
                                'def' => $def,
                                'name' => $def['key'],
                                'value' => $value,
                                'withLabel' => true
                            ])
                        @endif
                    </div>
                @endforeach
            </div>
        @endforeach

        <div class="buttons">
            <x-form.button type="submit" variant="primary" icon="save">
                Salvar configurações
            </x-form.button>
        </div>
    </x-form>
</div>
@endsection

@push('styles')
<style>
    /* Navegação por abas */
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
        color: var(--color-text-muted, #6b7280);
        font-size: 0.9rem;
        font-weight: 500;
        cursor: pointer;
        white-space: nowrap;
        border-bottom: 2px solid transparent;
        margin-bottom: -2px;
        transition: color 0.2s ease, border-color 0.2s ease;
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

    /* Painéis de abas */
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

    /* Grupos de configuração */
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

    .group-subtitle {
        margin-top: 2.5rem;
    }

    .group-subtitle + p {
        margin-bottom: 1.5rem;
    }

    .group-description {
        font-size: 0.875rem;
        color: #6b7280;
        margin: 0.25rem 0 0;
    }

    /* Container de campos */
    .form-group {
        margin-bottom: 1.25rem;
    }

    .form-input-narrow {
        max-width: 160px;
    }

    /* Imagem customizada */
    .image-type {
        display: flex;
        flex-direction: row;
        gap: 1rem;
        align-items: flex-start;
        margin-top: 0.375rem;
    }

    .image-preview img {
        max-width: 130px;
        border-radius: 0.375rem;
        border: 1px solid #e5e7eb;
        display: block;
    }

    .image-input {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        flex-grow: 1;
    }

    .remove-image-label,
    .remove-password-label {
        font-size: 0.875rem;
        color: #dc3545;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        margin-top: 0.25rem;
    }

    /* Campos desabilitados por dependência */
    .form-group.field-disabled {
        opacity: 0.45;
        pointer-events: none;
    }

    .form-group.field-disabled .form-label {
        color: #9ca3af;
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/dependency-manager.js') }}" defer></script>
<script src="{{ asset('js/settings.js') }}" defer></script>
@endpush
