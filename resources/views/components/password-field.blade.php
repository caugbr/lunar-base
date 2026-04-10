@props(['name', 'label', 'id', 'required' => false, 'confirm' => false])

<div class="password-component">
    <div class="form-group">
        <label for="{{ $id }}">{{ $label }}</label>
        <div class="password-field">
            <input type="password"
                   name="{{ $name }}"
                   id="{{ $id }}"
                   class="password-input"
                   autocomplete="new-password"
                   readonly
                   {{ $required ? 'required' : '' }}>
            <button type="button" class="toggle-password" data-target="{{ $id }}">
                <x-lucide-eye class="lucid-icon" />
            </button>
        </div>
        <small>Mínimo de 8 caracteres</small>
    </div>

    @if($confirm)
    <div class="form-group" style="margin-top: 16px;">
        <label for="{{ $id }}_confirmation">Confirmar {{ strtolower($label) }}</label>
        <div class="password-field">
            <input type="password"
                   name="{{ $name }}_confirmation"
                   id="{{ $id }}_confirmation"
                   class="password-input"
                   autocomplete="new-password"
                   readonly>
            <button type="button" class="toggle-password" data-target="{{ $id }}_confirmation">
                <x-lucide-eye class="lucid-icon" />
            </button>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configurar todos os campos de senha
    document.querySelectorAll('.password-input[readonly]').forEach(input => {
        input.addEventListener('focus', function() {
            setTimeout(() => this.removeAttribute('readonly'), 100);
        });
        input.addEventListener('blur', function() {
            this.setAttribute('readonly', true);
        });
    });

    // Configurar botões de visualização
    document.querySelectorAll('.toggle-password').forEach(button => {
        const targetId = button.dataset.target;
        const input = document.getElementById(targetId);

        const showPassword = () => input.setAttribute('type', 'text');
        const hidePassword = () => input.setAttribute('type', 'password');

        button.addEventListener('mousedown', (e) => {
            e.preventDefault();
            showPassword();
        });
        button.addEventListener('mouseup', hidePassword);
        button.addEventListener('mouseleave', hidePassword);
        button.addEventListener('touchstart', (e) => {
            e.preventDefault();
            showPassword();
        });
        button.addEventListener('touchend', hidePassword);
    });

    // Força da senha (apenas no campo principal)
    const mainPassword = document.querySelector('.password-input:not([id$="_confirmation"])');
    const confirmInput = document.getElementById(mainPassword?.id + '_confirmation');
    const submitBtn = mainPassword?.closest('form')?.querySelector('button[type="submit"]');

    if (mainPassword) {
        mainPassword.addEventListener('input', function() {
            const strength = checkPasswordStrength(this.value);
            const clsName = ['very-weak', 'weak', 'medium', 'strong', 'very-strong'][strength - 1];
            this.className = `password-input ${clsName}`;
            if (submitBtn) {
                if (confirmInput) {
                    submitBtn.disabled = this.value !== confirmInput.value || strength < 3;
                } else {
                    submitBtn.disabled = strength < 3;
                }
            }
        });
    }

    if (confirmInput) {
        confirmInput.addEventListener('input', function() {
            if (this.value === mainPassword?.value) {
                this.classList.remove('very-weak');
                if (submitBtn) submitBtn.disabled = false;
            } else {
                this.classList.add('very-weak');
                if (submitBtn) submitBtn.disabled = true;
            }
        });
    }
});

function checkPasswordStrength(password) {
    let score = 0;
    if (password.length >= 8) score++;
    if (password.match(/[a-z]/)) score++;
    if (password.match(/[A-Z]/)) score++;
    if (password.match(/[0-9]/)) score++;
    if (password.match(/[^a-zA-Z0-9]/)) score++;
    return score;
}
</script>
@endpush
@push('styles')
<style>
/* Container principal do componente */
.password-component {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 16px;
    align-items: baseline;
}

/* Quando tem confirmação, os campos ficam lado a lado */
.password-component .form-group:first-child {
    flex: 1;
}

.password-component .form-group:last-child {
    flex: 1;
}

/* Layout lado a lado apenas quando há confirmação */
.password-component:has(.form-group:last-child) {
    flex-direction: row;
    gap: 20px;
}

/* Para campos individuais (sem confirmação) */
.password-component:has(.form-group:only-child) {
    flex-direction: column;
}

.password-field {
    display: flex;
    align-items: center;
    gap: 8px;
    position: relative;
}

.password-field input {
    flex: 1;
    padding-right: 40px;
    width: 100%;
}

.toggle-password {
    position: absolute;
    right: 8px;
    background: none;
    border: none;
    cursor: pointer;
    padding: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

/* Força da senha */
.password-input.very-weak {
    background-color: #fff5f5;
    border-color: #fc8181;
}
.password-input.weak {
    background-color: #fffaf0;
    border-color: #fbd38d;
}
.password-input.medium {
    background-color: #fffff0;
    border-color: #f6e05e;
}
.password-input.strong {
    background-color: #f0fff4;
    border-color: #9ae6b4;
}
.password-input.very-strong {
    background-color: #f0fff4;
    border-color: #48bb78;
    background-image: linear-gradient(120deg, #48bb78 0%, #48bb78 100%);
    background-repeat: no-repeat;
    background-size: 100% 3px;
    background-position: bottom;
}

button[type="submit"]:disabled,
input[type="submit"]:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Ajustes para telas menores */
@media (max-width: 768px) {
    .password-component:has(.form-group:last-child) {
        flex-direction: column;
    }
}
</style>
@endpush
