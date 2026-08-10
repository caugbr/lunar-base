@php
    $inputName = $name ?? $def['key'];
    $inputValue = old($inputName, $value ?? ($def['default'] ?? ''));
    $options = $def['options'] ?? [];
    $attributes = $def['attributes'] ?? [];
@endphp

@switch($def['type'] ?? 'text')
    @case('icon')
        <x-icon-selector
            name="{{ $inputName }}"
            id="{{ str_replace(['[', ']'], ['_', ''], $inputName) }}"
            value="{{ $inputValue }}"
            can_clear="{{ $def['can_clear'] ?? true }}"
        />
        @break

    @case('page')
        <x-page-picker
            name="{{ $inputName }}"
            id="{{ str_replace(['[', ']'], ['_', ''], $inputName) }}"
            selected="{{ $inputValue }}"
        />
        @break

    @case('textarea')
        <textarea name="{{ $inputName }}" id="{{ str_replace(['[', ']'], ['_', ''], $inputName) }}" rows="3" class="form-input">{{ $inputValue }}</textarea>
        @break

    @case('select')
        <select name="{{ $inputName }}" id="{{ str_replace(['[', ']'], ['_', ''], $inputName) }}" class="form-input">
            @if(isset($def['placeholder']))
                <option value="">{{ $def['placeholder'] }}</option>
            @endif
            @foreach($options as $optValue => $optLabel)
                <option value="{{ $optValue }}" {{ $inputValue == $optValue ? 'selected' : '' }}>
                    {{ $optLabel }}
                </option>
            @endforeach
        </select>
        @break

    @case('radio')
        <div class="radio-group">
            @foreach($options as $optValue => $optLabel)
                <label class="radio-label">
                    <input type="radio" name="{{ $inputName }}" value="{{ $optValue }}" {{ $inputValue == $optValue ? 'checked' : '' }}>
                    <span>{{ $optLabel }}</span>
                </label>
            @endforeach
        </div>
        @break

    @case('checkbox')
        @if(!empty($options))
            @php
                $currentValues = is_array($inputValue) ? $inputValue : ($inputValue ? explode(',', $inputValue) : []);
            @endphp
            <div class="checkbox-group">
                @foreach($options as $optValue => $optLabel)
                    <label class="checkbox-label">
                        <input type="checkbox"
                            name="{{ $inputName }}[]"
                            value="{{ $optValue }}"
                            {{ in_array($optValue, $currentValues) ? 'checked' : '' }}
                        >
                        <span>{{ $optLabel }}</span>
                    </label>
                @endforeach
            </div>
        @else
            <label class="checkbox-label">
                <input type="hidden" name="{{ $inputName }}" value="0">
                <input type="checkbox" name="{{ $inputName }}" value="1" {{ $inputValue ? 'checked' : '' }}>
                <span>Sim</span>
            </label>
        @endif
        @break

    @case('switch')
        <x-switch
            name="{{ $inputName }}"
            active="{{ $def['active'] ?? 'Ativado' }}"
            inactive="{{ $def['inactive'] ?? 'Desativado' }}"
            checked="{{ $inputValue }}"
        />
        @break

    @case('number')
        <input type="number"
            name="{{ $inputName }}"
            id="{{ str_replace(['[', ']'], ['_', ''], $inputName) }}"
            value="{{ $inputValue }}"
            @foreach($attributes as $attr => $attrValue)
                {{ $attr }}="{{ $attrValue }}"
            @endforeach
            class="form-input form-input-narrow"
        >
        @break

    @case('url')
        <input type="url" name="{{ $inputName }}" id="{{ str_replace(['[', ']'], ['_', ''], $inputName) }}" value="{{ $inputValue }}" class="form-input" placeholder="{{ $def['placeholder'] ?? 'https://...' }}" autocomplete="off">
        @break

    @case('email')
        <input type="email" name="{{ $inputName }}" id="{{ str_replace(['[', ']'], ['_', ''], $inputName) }}" value="{{ $inputValue }}" class="form-input" placeholder="{{ $def['placeholder'] ?? 'email@exemplo.com' }}" autocomplete="off">
        @break

    @case('password')
        <div class="password-field">
            <input type="password" name="{{ $inputName }}" id="{{ str_replace(['[', ']'], ['_', ''], $inputName) }}" value="" class="form-input" placeholder="••••••••" autocomplete="new-password">
            <a href="#" class="show" title="Ver senha"><x-lucide-eye class="lucid-icon" /></a>
            <a href="#" class="hide" title="Esconder senha"><x-lucide-eye-off class="lucid-icon" /></a>
        </div>
        @if($value)
            <label class="remove-password-label">
                <input type="checkbox" name="remove_settings[{{ $def['key'] }}]" value="1">
                Remover senha atual
            </label>
            <small class="form-help">Senha configurada. Deixe em branco para manter, ou marque para remover.</small>
        @else
            <small class="form-help">Nenhuma senha configurada.</small>
        @endif
        @break

    @default
        <input type="text" name="{{ $inputName }}" id="{{ str_replace(['[', ']'], ['_', ''], $inputName) }}" value="{{ $inputValue }}" class="form-input" placeholder="{{ $def['placeholder'] ?? '' }}" autocomplete="off" spellcheck="false">
@endswitch
