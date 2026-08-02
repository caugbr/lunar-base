<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Support\RenderManager;

class Render extends Component
{
    public string $output;

    public function __construct(
        public string $name,
        public array $params = []
    ) {
        // Executa a renderização do bloco via RenderManager
        $this->output = RenderManager::render($this->name, $this->params);
    }

    /**
     * Retorna o template Blade inline.
     * Se o renderizador retornar HTML, exibe ele. Se estiver vazio, exibe o $slot de fallback.
     */
    public function render()
    {
        return <<<'BLADE'
@if(!empty(trim($output)))
{!! $output !!}
@else
{{ $slot }}
@endif
BLADE;
    }
}
