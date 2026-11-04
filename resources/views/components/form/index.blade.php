{{-- resources/views/components/form/index.blade.php --}}
@props([
    'method' => 'POST',
    'action' => '',
    'hasFiles' => false,
    'warnChanges' => false,
    'preventDouble' => true,
    'restoreEvent' => null,
])

@php
    $method = strtoupper($method);
    $spoofedMethod = in_array($method, ['PUT', 'PATCH', 'DELETE']);
    $realMethod = $spoofedMethod ? 'POST' : $method;
@endphp

@once
@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin/components/form/base.css') }}">
@endpush
@endonce

<form
    action="{{ $action }}"
    method="{{ $realMethod }}"
    @if($hasFiles) enctype="multipart/form-data" @endif
    @if($preventDouble) data-prevent-double-submit @endif
    @if($restoreEvent) data-restore-event="{{ $restoreEvent }}" @endif
    {{ $attributes }}
>
    @if($realMethod !== 'GET')
        @csrf
    @endif

    @if($spoofedMethod)
        @method($method)
    @endif

    @if($warnChanges)
        <x-form.warn-changes />
    @endif

    {{ $slot }}
</form>

@if($preventDouble)
@once
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Intercepta o envio do formulário
    document.addEventListener('submit', (e) => {
        const form = e.target;
        if (!form.hasAttribute('data-prevent-double-submit')) return;

        // Se o formulário já está sendo enviado, cancela submits subsequentes (ex: Enter rápido)
        if (form.dataset.submitting === 'true') {
            e.preventDefault();
            return;
        }

        if (!form.checkValidity()) return;

        // Marca como em envio
        form.dataset.submitting = 'true';

        // Pega os botões atrelados a este form
        const formButtons = Array.from(form.elements).filter(el => el.tagName === 'BUTTON');
        const activeBtn = e.submitter || formButtons.find(b => b.type === 'submit');

        setTimeout(() => {
            formButtons.forEach(btn => btn.disabled = true);
            if (activeBtn) activeBtn.classList.add('is-loading');
        }, 0);

        // Se for um form AJAX com evento de restauração
        const restoreEvent = form.dataset.restoreEvent;
        if (restoreEvent) {
            window.addEventListener(restoreEvent, () => {
                form.dataset.submitting = 'false';
                formButtons.forEach(btn => {
                    btn.disabled = false;
                    btn.classList.remove('is-loading');
                });
            }, { once: true });
        }
    });

    // 2. Histórico do navegador (bfcache)
    window.addEventListener('pageshow', (e) => {
        if (e.persisted) {
            document.querySelectorAll('form[data-prevent-double-submit]').forEach(form => {
                form.dataset.submitting = 'false';
                Array.from(form.elements)
                    .filter(el => el.tagName === 'BUTTON')
                    .forEach(btn => {
                        btn.disabled = false;
                        btn.classList.remove('is-loading');
                    });
            });
        }
    });
});
</script>
@endpush
@endonce
@endif
