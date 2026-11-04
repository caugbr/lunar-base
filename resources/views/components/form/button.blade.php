@props([
    'href' => null,
    'type' => 'button',
    'variant' => 'primary',
    'size' => null,
    'preventDouble' => false,
    'restoreEvent' => null,
    'icon' => null,
    'iconPosition' => 'left',
])

@php
    $isLink = !empty($href);
    $isTransparent = $variant === 'transparent';

    $baseClass = $isTransparent ? 'transparent-btn' : 'admin-btn';
    $variantClass = (!$isTransparent && $variant) ? 'admin-btn-' . $variant : '';
    $sizeClass = ($size === 'sm') ? 'admin-btn-sm' : '';

    $classes = array_filter([$baseClass, $variantClass, $sizeClass]);
    // O botão só previne duplo clique se explicitamente pedido ou se tiver restoreEvent
    $shouldPrevent = !$isLink && ($preventDouble || !empty($restoreEvent));
@endphp

@if($isLink)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>
        @if($icon && $iconPosition === 'left')
            <x-dynamic-component :component="'lucide-' . $icon" class="lucid-icon" />
        @endif

        <span class="btn-text">{{ $slot }}</span>

        @if($icon && $iconPosition === 'right')
            <x-dynamic-component :component="'lucide-' . $icon" class="lucid-icon" />
        @endif
    </a>
@else
    <button
        type="{{ $type }}"
        @if($shouldPrevent) data-single-action @endif
        @if($restoreEvent) data-restore-event="{{ $restoreEvent }}" @endif
        {{ $attributes->class($classes) }}
    >
        {{-- Spinner nativo sempre pronto caso entre em loading --}}
        <span class="btn-spinner" aria-hidden="true"></span>

        @if($icon && $iconPosition === 'left')
            <x-dynamic-component :component="'lucide-' . $icon" class="lucid-icon" />
        @endif

        <span class="btn-text">{{ $slot }}</span>

        @if($icon && $iconPosition === 'right')
            <x-dynamic-component :component="'lucide-' . $icon" class="lucid-icon" />
        @endif
    </button>
@endif

@if($shouldPrevent)
@once
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Trata única e exclusivamente o botão clicado
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('button[data-single-action]');
        if (!btn || btn.disabled) return;

        setTimeout(() => {
            btn.disabled = true;
            btn.classList.add('is-loading');

            const restoreEvent = btn.dataset.restoreEvent;
            if (restoreEvent) {
                window.addEventListener(restoreEvent, () => {
                    btn.disabled = false;
                    btn.classList.remove('is-loading');
                }, { once: true });
            }
        }, 0);
    });
});
</script>
@endpush
@endonce
@endif
