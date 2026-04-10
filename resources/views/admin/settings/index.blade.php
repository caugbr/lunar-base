@extends('admin.layout')

@section('header_title', 'Configurações')
@section('header_subtitle', 'Configure o sistema')

@section('content')
<div class="admin-card">
    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
        @csrf

        @foreach($groups as $groupName => $groupDefinitions)
            <div class="settings-group" style="margin-bottom: 2rem;">
                <h3 style="text-transform: capitalize; border-bottom: 1px solid #e5e7eb; padding-bottom: 0.5rem;">
                    {{ ucfirst($groupName) }}
                </h3>

                @foreach($groupDefinitions as $def)
                    @php
                        $value = old($def['key'], $def['value'] ?? $def['default'] ?? '');
                        $options = $def['options'] ?? [];
                        $attributes = $def['attributes'] ?? [];
                    @endphp

                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label for="{{ $def['key'] }}">{{ $def['label'] }}</label>

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

                            {{-- @case('image')
                                <div class="image-preview">
                                    @if($value)
                                        <img src="{{ asset($value) }}" style="max-width: 150px; margin-bottom: 10px; display: block;">
                                    @endif
                                    <input type="file" name="{{ $def['key'] }}" accept="image/*">
                                    <input type="hidden" name="{{ $def['key'] }}_current" value="{{ $value }}">
                                    <small>Deixe em branco para manter a imagem atual</small>
                                </div>
                                @break --}}
                                @case('image')
                                    <div class="image-type" style="/* display: flex; flex-direction: column; gap: 0.5rem; */">
                                        {{-- Preview da imagem atual --}}
                                        @if($value)
                                            <div class="image-preview" style="/* display: flex; align-items: center; gap: 1rem; */">
                                                <img src="{{ $value }}"
                                                    alt="{{ $def['label'] }}"
                                                    style="/* max-width: 150px; border-radius: 0.375rem; border: 1px solid #e5e7eb; */">

                                            </div>
                                        @endif

                                        <div class="image-input">

                                            {{-- Input para novo upload --}}
                                            <input type="file"
                                                name="{{ $def['key'] }}"
                                                accept="image/*"
                                                class="form-input">

                                            @if($value)
                                            {{-- Checkbox para remover a imagem --}}
                                            <label style="font-size: 0.875rem; color: #ef4444; cursor: pointer;">
                                                <input type="checkbox"
                                                    name="remove_settings[{{ $def['key'] }}]"
                                                    value="1"
                                                    style="margin-right: 0.25rem;">
                                                Remover imagem
                                            </label>
                                            @endif

                                            {{-- Hidden field com o PATH RELATIVO (não a URL!) --}}
                                            <input type="hidden"
                                                name="{{ $def['key'] }}_current"
                                                value="{{ $def['path'] ?? $value }}">

                                        </div>

                                    </div>
                                        <small style="color: #6b7280;">
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
        align-items: flex-end;
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
    /* .form-input {
        width: 100%;
        padding: 0.5rem;
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
    } */
</style>
@endpush
