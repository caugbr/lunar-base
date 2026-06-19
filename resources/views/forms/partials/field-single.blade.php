@php
    $value = old($def['key'], $def['value'] ?? $def['default'] ?? '');
    $options = $def['options'] ?? [];
    $attributes = $def['attributes'] ?? [];
    $required = !empty($def['required']) || (isset($def['rules']) && str_contains($def['rules'], 'required'));

    $prefix = $def['prefix'] ?? '';
    $suffix = $def['suffix'] ?? '';
    $cssClass = $def['css_class'] ?? '';
    $hasAddon = !empty($prefix) || !empty($suffix);
@endphp

{{-- Campos Ocultos não precisam de label nem wrapper --}}
@if(($def['type'] ?? '') === 'hidden')
    <input type="hidden" name="{{ $def['key'] }}" value="{{ $value }}">
@else
    <div class="form-group">
        <label for="{{ $def['key'] }}" class="field-label">
            {{ $def['label'] }}
            @if($required) <span class="required">*</span> @endif
        </label>

        {{-- Wrapper para Input Group (Prefixo/Sufixo) --}}
        @if($hasAddon)
            <div class="input-group">
                @if(!empty($prefix)) <span class="input-group-addon">{{ $prefix }}</span> @endif
        @endif

        @switch($def['type'])
            @case('textarea')
                <textarea name="{{ $def['key'] }}" id="{{ $def['key'] }}" rows="{{ $def['rows'] ?? 3 }}"
                    placeholder="{{ $def['placeholder'] ?? '' }}"
                    class="form-input {{ $cssClass }}"
                    @if($required) required @endif
                    @foreach($attributes as $attr => $attrValue) {{ $attr }}="{{ $attrValue }}" @endforeach
                >{{ $value }}</textarea>
                @break

            @case('select')
                <select name="{{ $def['key'] }}" id="{{ $def['key'] }}" class="form-input {{ $cssClass }}"
                    @if($required) required @endif
                    @foreach($attributes as $attr => $attrValue) {{ $attr }}="{{ $attrValue }}" @endforeach
                >
                    @if(empty($def['no_placeholder'])) <option value="">Selecione...</option> @endif
                    @foreach($options as $optValue => $optLabel)
                        <option value="{{ $optValue }}" {{ $value == $optValue ? 'selected' : '' }}>{{ $optLabel }}</option>
                    @endforeach
                </select>
                @break

            @case('radio')
                <div class="radio-group">
                    @foreach($options as $optValue => $optLabel)
                        <label class="radio-label">
                            <input type="radio" name="{{ $def['key'] }}" value="{{ $optValue }}" {{ $value == $optValue ? 'checked' : '' }} @if($required) required @endif>
                            <span>{{ $optLabel }}</span>
                        </label>
                    @endforeach
                </div>
                @break

            @case('checkbox')
                @if(!empty($options))
                    @php $currentValues = is_array($value) ? $value : ($value ? explode(',', $value) : []); @endphp
                    <div class="checkbox-group">
                        @foreach($options as $optValue => $optLabel)
                            <label class="checkbox-label">
                                <input type="checkbox" name="{{ $def['key'] }}[]" value="{{ $optValue }}" {{ in_array($optValue, $currentValues) ? 'checked' : '' }}>
                                <span>{{ $optLabel }}</span>
                            </label>
                        @endforeach
                    </div>
                @else
                    <label class="checkbox-label">
                        <input type="hidden" name="{{ $def['key'] }}" value="0">
                        <input type="checkbox" name="{{ $def['key'] }}" value="1" {{ $value ? 'checked' : '' }} @if($required) required @endif>
                        <span>{{ $def['checkbox_label'] ?? 'Sim' }}</span>
                    </label>
                @endif
                @break

            @case('switch')
                <x-switch checked="{{ $value }}" name="{{ $def['key'] }}" id="{{ $def['key'] }}" active="{{ $def['active'] ?? 'Sim' }}" inactive="{{ $def['inactive'] ?? 'Não' }}" />
                @break

            @case('number')
                <input type="number" name="{{ $def['key'] }}" id="{{ $def['key'] }}" value="{{ $value }}"
                    placeholder="{{ $def['placeholder'] ?? '' }}"
                    class="form-input form-input-narrow {{ $cssClass }}"
                    min="{{ $def['min'] ?? '' }}" max="{{ $def['max'] ?? '' }}" step="{{ $def['step'] ?? '' }}"
                    @if($required) required @endif
                    @foreach($attributes as $attr => $attrValue) {{ $attr }}="{{ $attrValue }}" @endforeach
                >
                @break

            @case('url')
                <input type="url" name="{{ $def['key'] }}" id="{{ $def['key'] }}" value="{{ $value }}" placeholder="{{ $def['placeholder'] ?? 'https://...' }}" class="form-input {{ $cssClass }}" @if($required) required @endif @foreach($attributes as $attr => $attrValue) {{ $attr }}="{{ $attrValue }}" @endforeach>
                @break

            @case('email')
                <input type="email" name="{{ $def['key'] }}" id="{{ $def['key'] }}" value="{{ $value }}" placeholder="{{ $def['placeholder'] ?? 'email@exemplo.com' }}" class="form-input {{ $cssClass }}" @if($required) required @endif @foreach($attributes as $attr => $attrValue) {{ $attr }}="{{ $attrValue }}" @endforeach>
                @break

            @case('tel')
            @case('phone')
                <input type="tel" name="{{ $def['key'] }}" id="{{ $def['key'] }}" value="{{ $value }}" placeholder="{{ $def['placeholder'] ?? '(00) 00000-0000' }}" class="form-input {{ $cssClass }}" @if($required) required @endif @foreach($attributes as $attr => $attrValue) {{ $attr }}="{{ $attrValue }}" @endforeach>
                @break

            @default
                <input type="text" name="{{ $def['key'] }}" id="{{ $def['key'] }}" value="{{ $value }}" placeholder="{{ $def['placeholder'] ?? '' }}" class="form-input {{ $cssClass }}" @if($required) required @endif @foreach($attributes as $attr => $attrValue) {{ $attr }}="{{ $attrValue }}" @endforeach>
        @endswitch

        @if($hasAddon)
                @if(!empty($suffix)) <span class="input-group-addon">{{ $suffix }}</span> @endif
            </div>
        @endif

        @if(!empty($def['description']))
            <small class="form-help">{{ $def['description'] }}</small>
        @endif

        @error($def['key'])
            <small class="error">{{ $message }}</small>
        @enderror
    </div>
@endif
