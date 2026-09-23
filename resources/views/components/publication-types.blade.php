@props([
    'name',
    'value' => null,
    'unique' => false,
    'id' => null,
])

@php
    use App\Support\PublicationTypes;

    // Busca a lista oficial [key => label]
    $types = PublicationTypes::labels();

    // Converte o id base
    $baseId = $id ?? str_replace(['[', ']'], ['_', ''], $name);

    // Normaliza o valor para array caso seja múltiplo (suporta array, JSON ou string separada por vírgula)
    $selectedValues = [];
    if (! $unique) {
        if (is_array($value)) {
            $selectedValues = $value;
        } elseif (is_string($value) && !empty($value)) {
            // Tenta decodificar JSON, se falhar faz explode por vírgula
            $decoded = json_decode($value, true);
            $selectedValues = is_array($decoded) ? $decoded : explode(',', $value);
        }
    }
@endphp

@if($unique)
    {{-- SE FOR ÚNICO: Renderiza como RADIO --}}
    <div class="radio-group" id="{{ $baseId }}">
        @foreach($types as $typeKey => $typeLabel)
            @php $radioId = "{$baseId}_{$typeKey}"; @endphp
            <label class="radio-label" for="{{ $radioId }}">
                <input
                    type="radio"
                    id="{{ $radioId }}"
                    name="{{ $name }}"
                    value="{{ $typeKey }}"
                    {{ $value == $typeKey ? 'checked' : '' }}
                >
                <span>{{ $typeLabel }}</span>
            </label>
        @endforeach
    </div>
@else
    {{-- SE FOR MÚLTIPLO: Renderiza como CHECKBOX --}}
    <div class="checkbox-group" id="{{ $baseId }}">
        @foreach($types as $typeKey => $typeLabel)
            @php $checkboxId = "{$baseId}_{$typeKey}"; @endphp
            <label class="checkbox-label" for="{{ $checkboxId }}">
                <input
                    type="checkbox"
                    id="{{ $checkboxId }}"
                    name="{{ $name }}[]"
                    value="{{ $typeKey }}"
                    {{ in_array($typeKey, $selectedValues) ? 'checked' : '' }}
                >
                <span>{{ $typeLabel }}</span>
            </label>
        @endforeach
    </div>
@endif
