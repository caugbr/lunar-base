@props([
    'selector' => '.post-content, .page-content',
    'variation' => 2, // Quantidade de passos de aumento antes de retornar ao original (ex: original -> +4px -> +8px -> original)
    'step' => 4       // Valor (em pixels) que será adicionado a cada clique/passo
])

<div class="text-size" id="text-size-controller">
    <a href="#" id="text-size-toggle" title="Alternar tamanho do texto" aria-label="Aumentar tamanho do texto">
        <x-lucide-a-arrow-up class="lucid-icon" />
    </a>
</div>

@push('accessibility-styles')
<style>
    .text-size {
        display: inline-flex;
        align-items: center;
        margin: auto;
    }
    .text-size a {
        text-decoration: none;
        display: inline-flex;
        width: 32px;
        height: 32px;
        justify-content: center;
        align-items: center;
        border: 1px solid currentColor;
        border-radius: 6px;
        transition: background-color 0.2s, color 0.2s, transform 0.1s;
    }
    .text-size a:hover {
        background-color: var(--color-border-hover, rgba(0, 0, 0, 0.05));
    }
    .text-size a:active {
        transform: scale(0.95);
    }
    .text-size .lucid-icon {
        width: 20px;
        height: 20px;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const textElements = document.querySelectorAll('{{ $selector }}');
        const maxVariation = {{ $variation }};
        const stepValue = {{ $step }};
        let currentStep = 0; // Passo atual: 0 (original) até maxVariation

        // 1. Salva o tamanho original calculado de cada elemento
        textElements.forEach(elem => {
            const computedSize = window.getComputedStyle(elem).fontSize;
            elem.dataset.originalSize = parseFloat(computedSize) || 16;
            elem.dataset.sizeUnit = computedSize.match(/[a-zA-Z%]+$/)?.[0] || 'px';
        });

        // 2. Controla o clique do botão único (ciclo: 0 -> 1 -> 2 -> 0)
        const toggleBtn = document.getElementById('text-size-toggle');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', event => {
                event.preventDefault();

                if (currentStep < maxVariation) {
                    currentStep++;
                } else {
                    currentStep = 0; // Limite atingido, reseta de volta ao original
                }

                updateSizes();
            });
        }

        // 3. Aplica o novo tamanho
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
