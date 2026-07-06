<?php

// namespace App\View\Components;

// use Illuminate\View\Component;
// use App\Support\HookManager;

// class Hook extends Component
// {
//     public function __construct(
//         public string $name,
//         public array $params = []
//     ) {}

//     /**
//      * Como estamos retornando uma string diretamente,
//      * o Laravel não buscará por um arquivo .blade.php.
//      */
//     public function render()
//     {
//         return HookManager::render($this->name, $this->params);
//     }
// }

namespace App\View\Components;

use Illuminate\View\Component;
use App\Support\HookManager;

class Hook extends Component
{
    public string $output;

    public function __construct(
        public string $name,
        public array $params = []
    ) {
        // Executa o hook durante a construção do componente
        $this->output = HookManager::render($this->name, $this->params);
    }

    /**
     * Retorna um template Blade inline.
     * Se o hook tiver conteúdo, exibe ele. Se estiver vazio, exibe o $slot padrão.
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
