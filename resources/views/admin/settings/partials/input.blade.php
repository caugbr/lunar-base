@php
    $inputName = $name ?? $def['key'];
    $inputId = str_replace(['[', ']', '.'], ['_', '', '_'], $inputName);
    $inputValue = old($inputName, $value ?? ($def['default'] ?? ''));
    $options = $def['options'] ?? [];

    $withLabel = $withLabel ?? false;
    $label = $withLabel ? ($def['label'] ?? null) : null;
    $help = $withLabel ? ($def['description'] ?? null) : null;
@endphp

@switch($def['type'] ?? 'text')
    @case('icon')
        <x-picker.icon
            name="{{ $inputName }}"
            id="{{ $inputId }}"
            :value="$inputValue"
            :label="$label"
            :help="$help"
            :can-clear="$def['can_clear'] ?? true"
        />
        @break

    @case('publication_types')
        <x-picker.pubtypes
            name="{{ $inputName }}"
            id="{{ $inputId }}"
            :value="$inputValue"
            :unique="$def['unique'] ?? false"
            :label="$label"
            :help="$help"
            inline
        />
        @break

    @case('page')
        <x-picker.page
            name="{{ $inputName }}"
            id="{{ $inputId }}"
            :value="$inputValue"
            :label="$label"
            :help="$help"
            :placeholder="$def['placeholder'] ?? '-- Selecionar página --'"
        />
        @break

    @case('textarea')
        <x-form.textarea
            name="{{ $inputName }}"
            id="{{ $inputId }}"
            :value="$inputValue"
            :label="$label"
            :help="$help"
            :rows="$def['rows'] ?? 3"
            :editable="$def['editable'] ?? false"
        />
        @break

    @case('select')
        <x-form.select
            name="{{ $inputName }}"
            id="{{ $inputId }}"
            :options="$options"
            :value="$inputValue"
            :label="$label"
            :help="$help"
            :placeholder="$def['placeholder'] ?? null"
            :multiple="$def['multiple'] ?? false"
            :allow-insert="$def['allow_insert'] ?? false"
            :insert-label="$def['insert_label'] ?? 'Inserir novo...'"
            :insert-placeholder="$def['insert_placeholder'] ?? 'Novo valor'"
        />
        @break

    @case('radio')
        <x-form.radio
            name="{{ $inputName }}"
            id="{{ $inputId }}"
            :options="$options"
            :value="$inputValue"
            :label="$label"
            :help="$help"
            :inline="$def['inline'] ?? true"
        />
        @break

    @case('checkbox')
        @if(!empty($options))
            @php
                $currentValues = is_array($inputValue) ? $inputValue : ($inputValue ? explode(',', (string) $inputValue) : []);
            @endphp
            <x-form.checkbox
                name="{{ $inputName }}"
                id="{{ $inputId }}"
                :options="$options"
                :value="$currentValues"
                :label="$label"
                :help="$help"
                :inline="$def['inline'] ?? false"
            />
        @else
            <x-form.checkbox
                name="{{ $inputName }}"
                id="{{ $inputId }}"
                value="1"
                :checked="(bool) $inputValue"
                :text="$def['text'] ?? 'Sim'"
                :label="$label"
                :help="$help"
            />
        @endif
        @break

    @case('switch')
        <x-form.switch
            name="{{ $inputName }}"
            id="{{ $inputId }}"
            :checked="(bool) $inputValue"
            :active="$def['active'] ?? 'Ativado'"
            :inactive="$def['inactive'] ?? 'Desativado'"
            :label="$label"
            :help="$help"
        />
        @break

    @case('image')
        <div class="image-type">
            @if($value)
                <div class="image-preview">
                    <img src="{{ $value }}" alt="{{ $def['label'] ?? '' }}">
                </div>
            @endif

            <div class="image-input">
                <x-form.upload
                    name="{{ $def['key'] }}"
                    id="{{ $inputId }}"
                    accept="image/*"
                    button-label="Escolher imagem"
                    message="Solte uma nova imagem para substituir"
                />

                @if($value)
                    <label class="remove-image-label">
                        <input type="checkbox" name="remove_settings[{{ $def['key'] }}]" value="1">
                        Remover imagem
                    </label>
                @endif

                <input type="hidden" name="{{ $def['key'] }}_current" value="{{ $def['path'] ?? $value }}">
            </div>
        </div>
        <small class="form-help image-help">
            @if($value)
                Marque "Remover imagem" para apagar, ou selecione um novo arquivo para substituir.
            @else
                Selecione uma imagem para enviar.
            @endif
        </small>
        @break

    @case('number')
        <x-form.text
            type="number"
            name="{{ $inputName }}"
            id="{{ $inputId }}"
            :value="$inputValue"
            :label="$label"
            :help="$help"
            :min="$def['attributes']['min'] ?? null"
            :max="$def['attributes']['max'] ?? null"
            :step="$def['attributes']['step'] ?? null"
            class="form-input-narrow"
        />
        @break

    @case('url')
        <x-form.text
            type="url"
            name="{{ $inputName }}"
            id="{{ $inputId }}"
            :value="$inputValue"
            :label="$label"
            :help="$help"
            :placeholder="$def['placeholder'] ?? 'https://...'"
            autocomplete="off"
        />
        @break

    @case('email')
        <x-form.text
            type="email"
            name="{{ $inputName }}"
            id="{{ $inputId }}"
            :value="$inputValue"
            :label="$label"
            :help="$help"
            :placeholder="$def['placeholder'] ?? 'email@exemplo.com'"
            autocomplete="off"
        />
        @break

    @case('color')
        <x-form.color
            name="{{ $inputName }}"
            id="{{ $inputId }}"
            :value="$inputValue ?: '#000000'"
            :label="$label"
            :help="$help"
        />
        @break

    @case('password')
        @php
            $passwordHelp = $value
                ? 'Senha configurada. Deixe em branco para manter, ou marque para remover.'
                : 'Nenhuma senha configurada.';
        @endphp
        <x-form.password
            name="{{ $inputName }}"
            id="{{ $inputId }}"
            :label="$label"
            :help="$help ?? $passwordHelp"
            placeholder="••••••••"
        />
        @if($value)
            <label class="remove-password-label">
                <input type="checkbox" name="remove_settings[{{ $def['key'] }}]" value="1">
                Remover senha atual
            </label>
        @endif
        @break

    @default
        <x-form.text
            type="text"
            name="{{ $inputName }}"
            id="{{ $inputId }}"
            :value="$inputValue"
            :label="$label"
            :help="$help"
            :placeholder="$def['placeholder'] ?? ''"
            autocomplete="off"
            spellcheck="false"
        />
@endswitch
