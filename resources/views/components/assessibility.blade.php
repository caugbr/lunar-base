@props([
    'useSwitchThemes' => setting('reading.switch_themes', 0),
    'useTextSize' => setting('reading.increase_text_size', 0),
    'useVlibras' => setting('reading.vlibras', 0),
    'positionClass' => setting('reading.position', 'right-middle'),
    'textSizeSteps' => setting('reading.text_size_steps', 2),
    'textSizeStepValue' => setting('reading.text_size_step_value', 4),
])

@php
@endphp
@if($useSwitchThemes || $useTextSize || $useVlibras)
<div class="accessibility {{ $positionClass }}">
    @if($useSwitchThemes)
    <x-switch-theme />
    @endif
    @if($useTextSize)
    <x-text-size :variation="$textSizeSteps" :step="$textSizeStepValue" />
    @endif
    @if($useVlibras)
    <x-vlibras />
    @endif
</div>
<style>
/* --- Estilos Base + Padrão (right-middle) --- */
.accessibility {
    position: fixed;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    z-index: 99999;

    /* Comportamento Padrão (Fallback): Direita e Centralizado Verticalmente */
    right: 10px;
    left: auto;
    top: 50%;
    bottom: auto;
    transform: translateY(-50%);
}

/* --- ALINHAMENTO VERTICAL --- */

/* Superior (Esquerda / Direita) */
.accessibility.left-top,
.accessibility.right-top {
    top: 10px;
    bottom: auto;
    transform: none;
}

/* Centralizado (Esquerda / Direita) */
.accessibility.left-middle,
.accessibility.right-middle {
    top: 50%;
    bottom: auto;
    transform: translateY(-50%);
}

/* Inferior (Esquerda / Direita) */
.accessibility.left-bottom,
.accessibility.right-bottom {
    top: auto;
    bottom: 10px;
    transform: none;
}

/* --- ALINHAMENTO HORIZONTAL --- */

/* Força alinhamento à Esquerda */
.accessibility.left-top,
.accessibility.left-middle,
.accessibility.left-bottom {
    left: 10px;
    right: auto;
}

/* Força alinhamento à Direita (útil para sobrescrever estados anteriores) */
.accessibility.right-top,
.accessibility.right-middle,
.accessibility.right-bottom {
    right: 10px;
    left: auto;
}

.accessibility div[vw] {
    transform: none !important;
    position: static !important;
    margin: 0 !important;
}
</style>
@endif
