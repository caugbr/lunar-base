@props([
    'selector' => '.post-content, .page-content',
    'variation' => 2, // Quantidade máxima de passos permitidos (ex: até 2 passos para cima ou para baixo)
    'style' => 'vertical',
    'step' => 4 // Valor (em pixels) que será alterado a cada clique/passo
])

<div class="text-size ts-{{ $style }}" id="text-size-controller">
    <a href="#" data-direction="up" title="Aumentar texto">
        <x-lucide-a-arrow-up class="lucid-icon" />
    </a>
    <a href="#" data-direction="down" title="Diminuir texto">
        <x-lucide-a-arrow-down class="lucid-icon" />
    </a>
</div>

{{-- @push('styles') --}}
<style>
    .text-size {
        display: inline-flex;
        flex-direction: row;
        gap: 0.5rem;
    }
    .text-size.ts-vertical {
        flex-direction: column;
        margin: auto;
    }
    .text-size a {
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        display: inline-flex;
        width: 28px;
        height: 28px;
        justify-content: center;
        align-items: center;
        border: 1px solid currentColor;
        border-radius: 6px;
    }
    .text-size .lucid-icon {
        width: 20px;
        height: 20px;
    }
</style>
{{-- @endpush --}}

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const textElements = document.querySelectorAll('{{ $selector }}');
        const maxVariation = {{ $variation }};
        const stepValue = {{ $step }};
        let currentStep = 0;

        textElements.forEach(elem => {
            const computedSize = window.getComputedStyle(elem).fontSize;
            elem.dataset.originalSize = parseFloat(computedSize) || 16;
            elem.dataset.sizeUnit = computedSize.match(/[a-zA-Z%]+$/)?.[0] || 'px';
        });

        document.querySelectorAll('#text-size-controller a').forEach(a => {
            a.addEventListener('click', event => {
                event.preventDefault();
                const direction = a.dataset.direction;

                if (direction === 'up' && currentStep < maxVariation) {
                    currentStep++;
                    updateSizes();
                } else if (direction === 'down' && currentStep > -maxVariation) {
                    currentStep--;
                    updateSizes();
                }
            });
        });

        function updateSizes() {
            textElements.forEach(elem => {
                const original = parseFloat(elem.dataset.originalSize);
                const unit = elem.dataset.sizeUnit;
                const newSize = original + (currentStep * stepValue);
                elem.style.fontSize = newSize + unit;
            });
        }
    });
</script>
@endpush
