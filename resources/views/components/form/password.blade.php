@props([
    'name',
    'label' => 'Senha',
    'required' => false,
    'confirm' => false,
    'minLength' => null,
    'minScore' => null,
    'help' => null,
    'autocomplete' => null, // Nova prop
])

@php
    $dotName = str_replace(['[', ']'], ['.', ''], $name);
    $dotName = rtrim($dotName, '.');
    $id = $attributes->get('id', str_replace(['[', ']', '.'], ['_', '', '_'], $name));
    $hasError = $errors->has($dotName);

    $confirmName = $name . '_confirmation';
    $confirmDotName = $dotName . '_confirmation';
    $confirmId = $id . '_confirmation';
    $hasConfirmError = $errors->has($confirmDotName);

    $defaultHelp = $help;
    if ($defaultHelp === null && $minLength) {
        $defaultHelp = "Mínimo de {$minLength} caracteres";
    }

    // Se não especificado: se tiver confirmação ou regras de força = new-password, senão = current-password
    $resolvedAutocomplete = $autocomplete ?? ($confirm || $minLength || $minScore ? 'new-password' : 'current-password');
    $hasMeter = $minLength !== null || $minScore !== null;
@endphp

<div
    class="password-component @if($confirm) has-confirmation @endif"
    data-password-component
    data-min-length="{{ $minLength ?? '' }}"
    data-min-score="{{ $minScore ?? '' }}"
>
    <div class="form-field">
        <label for="{{ $id }}" class="form-label">
            {{ $label }}
            @if($required)
                <span class="form-required">*</span>
            @endif
        </label>

        <div class="password-field-wrapper">
            <input
                type="password"
                name="{{ $name }}"
                id="{{ $id }}"
                autocomplete="{{ $resolvedAutocomplete }}"
                @if($required) required @endif
                data-password-main
                {{ $attributes->class(['form-control', 'password-input', 'is-invalid' => $hasError]) }}
            >
            <button
                type="button"
                class="password-toggle-btn"
                data-password-toggle
                title="Exibir senha"
                aria-label="Exibir senha"
            >
                <x-lucide-eye class="lucid-icon icon-eye" />
                <x-lucide-eye-off class="lucid-icon icon-eye-off" />
            </button>
        </div>

        @if($hasMeter)
            <div class="password-strength-meter" data-strength-meter>
                <div class="password-strength-bar" data-strength-bar></div>
            </div>
        @endif

        @if($defaultHelp)
            <small class="form-help">{{ $defaultHelp }}</small>
        @endif

        @error($dotName)
            <small class="form-error">{{ $message }}</small>
        @enderror
    </div>

    @if($confirm)
        <div class="form-field">
            <label for="{{ $confirmId }}" class="form-label">
                Confirmar {{ mb_strtolower($label) }}
                @if($required)
                    <span class="form-required">*</span>
                @endif
            </label>

            <div class="password-field-wrapper">
                <input
                    type="password"
                    name="{{ $confirmName }}"
                    id="{{ $confirmId }}"
                    autocomplete="new-password"
                    @if($required) required @endif
                    data-password-confirm
                    class="form-control password-input @if($hasConfirmError) is-invalid @endif"
                >
                <button
                    type="button"
                    class="password-toggle-btn"
                    data-password-toggle
                    title="Exibir senha"
                    aria-label="Exibir senha"
                >
                    <x-lucide-eye class="lucid-icon icon-eye" />
                    <x-lucide-eye-off class="lucid-icon icon-eye-off" />
                </button>
            </div>

            @error($confirmDotName)
                <small class="form-error">{{ $message }}</small>
            @enderror
        </div>
    @endif
</div>

@once
@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin/components/form/password.css') }}">
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-password-component]').forEach(container => {
        const mainInput = container.querySelector('[data-password-main]');
        const confirmInput = container.querySelector('[data-password-confirm]');
        const strengthBar = container.querySelector('[data-strength-bar]');
        const toggleButtons = container.querySelectorAll('[data-password-toggle]');
        const form = container.closest('form');
        const submitBtn = form ? form.querySelector('button[type="submit"], input[type="submit"]') : null;

        const minLengthConfig = container.dataset.minLength ? parseInt(container.dataset.minLength, 10) : null;
        const minScoreConfig = container.dataset.minScore ? parseInt(container.dataset.minScore, 10) : null;

        toggleButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                const input = btn.previousElementSibling;
                if (!input) return;

                const willReveal = input.getAttribute('type') === 'password';
                input.setAttribute('type', willReveal ? 'text' : 'password');

                btn.classList.toggle('is-revealed', willReveal);
                btn.setAttribute('title', willReveal ? 'Ocultar senha' : 'Exibir senha');
                btn.setAttribute('aria-label', willReveal ? 'Ocultar senha' : 'Exibir senha');
            });
        });

        const evaluateStrength = (password) => {
            let score = 0;
            const threshold = minLengthConfig || 8;
            if (password.length >= threshold) score++;
            if (/[a-z]/.test(password)) score++;
            if (/[A-Z]/.test(password)) score++;
            if (/[0-9]/.test(password)) score++;
            if (/[^a-zA-Z0-9]/.test(password)) score++;
            return score;
        };

        const updateState = () => {
            const password = mainInput.value;
            const score = password.length > 0 ? evaluateStrength(password) : 0;

            if (strengthBar) {
                strengthBar.className = 'password-strength-bar';
                if (score > 0) {
                    const levels = ['level-1', 'level-2', 'level-3', 'level-4', 'level-5'];
                    strengthBar.classList.add(levels[score - 1]);
                }
            }

            if (!submitBtn) return;

            if (password.length === 0) {
                if (confirmInput) {
                    confirmInput.classList.remove('is-invalid');
                }
                submitBtn.disabled = false;
                return;
            }

            let isValid = true;

            if (minLengthConfig !== null && password.length < minLengthConfig) {
                isValid = false;
            }

            if (minScoreConfig !== null && score < minScoreConfig) {
                isValid = false;
            }

            if (confirmInput && confirmInput.value.length > 0) {
                const match = confirmInput.value === password;
                confirmInput.classList.toggle('is-invalid', !match);
                isValid = isValid && match;
            } else if (confirmInput && confirmInput.hasAttribute('required')) {
                isValid = false;
            }

            const shouldControlSubmit = minLengthConfig !== null || minScoreConfig !== null || confirmInput !== null;
            if (shouldControlSubmit) {
                submitBtn.disabled = !isValid;
            }
        };

        mainInput.addEventListener('input', updateState);
        if (confirmInput) {
            confirmInput.addEventListener('input', updateState);
        }
    });
});
</script>
@endpush
@endonce
