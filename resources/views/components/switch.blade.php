@props(['name', 'id' => '', 'checked' => 0, 'disabled' => 0, 'active' => 'Ativado', 'inactive' => 'Desativado'])

<label class="switch-label">
    <input type="hidden" name="{{ $name }}" value="0">
    <input type="checkbox"
        name="{{ $name }}"
        id="{{ $id }}"
        value="1"
        {{ $checked ? 'checked' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        class="switch-input">
    <span class="switch-track">
        <span class="switch-thumb"></span>
    </span>
    <span class="switch-text"
        data-active="{{ $active }}"
        data-inactive="{{ $inactive }}">
    </span>
</label>

@once
@push('styles')
<style>
.switch-label {
    display: inline-flex !important;
    align-items: center;
    gap: 0.75rem;
    cursor: pointer;
    position: relative;
    top: 8px;
}

.switch-label + small {
    display: block;
    margin-top: 14px;
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
.switch-input:disabled + .switch-track {
    opacity: 0.6;
    pointer-events: none;
}

.switch-label:has(.switch-input:disabled) {
    cursor: default;
}

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
</style>
@endpush
@endonce
