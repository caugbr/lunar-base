@extends('admin.layout')

@section('header_title', 'Configurações')
@section('header_subtitle', 'Configure o sistema')

@section('content')
<div class="admin-card">
    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
        @csrf
        {{-- Loop sobre os GRUPOS (nível 1) --}}
        @foreach($groups as $groupKey => $group)
            <div class="settings-group" style="margin-bottom: 2.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid #e5e7eb;">

                {{-- Cabeçalho do Grupo --}}
                <div style="margin-bottom: 1.5rem;">
                    <h3 style="display: flex; align-items: center; gap: 0.5rem; font-size: 1.25rem; font-weight: 600; color: #1f2937;">
                        @if(isset($group['icon']))
                            <x-dynamic-component component="lucide-{{ $group['icon'] }}" class="lucid-icon" />
                             {{-- style="width: 20px; height: 20px; color: #6b7280;" --}}
                            {{-- <x-lucide-{{ $group['icon'] }} class="lucid-icon" /> --}}
                        @endif
                        {{ $group['title'] }}
                    </h3>
                    @if(isset($group['description']))
                        <p style="font-size: 0.875rem; color: #6b7280; margin: 0.25rem 0 0;">{{ $group['description'] }}</p>
                    @endif
                </div>

                {{-- Loop sobre os CAMPOS do grupo (nível 2) --}}
                @foreach($group['fields'] as $def)
                    @php
                        $value = old($def['key'], $def['value'] ?? $def['default'] ?? '');
                        $options = $def['options'] ?? [];
                        $attributes = $def['attributes'] ?? [];
                    @endphp

                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label for="{{ $def['key'] }}" style="display: block; font-weight: 500; margin-bottom: 0.25rem; color: #374151;">
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
                                        <label style="display: inline-flex; align-items: center; gap: 0.25rem; margin-right: 1rem;">
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
                                            <label style="display: inline-flex; align-items: center; gap: 0.25rem; margin-right: 1rem;">
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
                                    <label style="display: flex; align-items: center; gap: 0.5rem;">
                                        <input type="hidden" name="{{ $def['key'] }}" value="0">
                                        <input type="checkbox" name="{{ $def['key'] }}" value="1" {{ $value ? 'checked' : '' }}>
                                        <span>Sim</span>
                                    </label>
                                @endif
                                @break

                            @case('number')
                                <input type="number"
                                    name="{{ $def['key'] }}"
                                    id="{{ $def['key'] }}"
                                    value="{{ $value }}"
                                    @foreach($attributes as $attr => $attrValue)
                                        {{ $attr }}="{{ $attrValue }}"
                                    @endforeach
                                    class="form-input"
                                    style="width: 150px;">
                                @break

                            @case('image')
                                <div class="image-type" style="display: flex; flex-direction: row; gap: 1rem; align-items: flex-start;">
                                    {{-- Preview da imagem atual --}}
                                    @if($value)
                                        <div class="image-preview">
                                            <img src="{{ $value }}"
                                                alt="{{ $def['label'] }}"
                                                style="max-width: 130px; border-radius: 0.375rem; border: 1px solid #e5e7eb;">
                                        </div>
                                    @endif

                                    <div class="image-input" style="display: flex; flex-direction: column; gap: 0.5rem; flex-grow: 1;">
                                        <x-upload-area name="{{ $def['key'] }}" />

                                        @if($value)
                                            <label style="font-size: 0.875rem; color: #ef4444; cursor: pointer;">
                                                <input type="checkbox"
                                                    name="remove_settings[{{ $def['key'] }}]"
                                                    value="1"
                                                    style="margin-right: 0.25rem;">
                                                Remover imagem
                                            </label>
                                        @endif

                                        {{-- Hidden field com o PATH RELATIVO --}}
                                        <input type="hidden"
                                            name="{{ $def['key'] }}_current"
                                            value="{{ $def['path'] ?? $value }}">
                                    </div>
                                </div>
                                <small style="color: #6b7280; display: block; margin-top: 0.5rem;">
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

                            @default
                                <input type="text" name="{{ $def['key'] }}" id="{{ $def['key'] }}" value="{{ $value }}" class="form-input">
                        @endswitch

                        @if(!empty($def['description']))
                            <small class="form-help">{{ $def['description'] }}</small>
                        @endif

                        @error($def['key'])
                            <small class="error" style="color: #ef4444; display: block;">{{ $message }}</small>
                        @enderror
                    </div>
                @endforeach
            </div>
        @endforeach

        <button type="submit" class="admin-btn admin-btn-primary">
            <x-lucide-save class="lucid-icon" /> Salvar Configurações
        </button>
    </form>
</div>
@endsection

@push('styles')
<style>
    .form-help {
        display: block;
        font-size: 0.75rem;
        color: #6b7280;
        margin-top: 0.25rem;
    }
    .image-type {
        display: flex;
        flex-direction: row;
        gap: 1rem;
        align-items: center;
    }
    .image-input {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        flex-grow: 3;
    }
    .image-preview img {
        width: 130px;
        height: auto;
    }
    /* Separação visual entre grupos */
    .settings-group {
        border-bottom: 1px solid #e5e7eb;
        padding-bottom: 1.5rem;
        margin-bottom: 2.5rem;
    }
    .settings-group:last-child {
        border-bottom: none;
        padding-bottom: 0;
        margin-bottom: 0;
    }
</style>
@endpush
