<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Support\HookManager;

class Hook extends Component
{
    public function __construct(
        public string $name,
        public array $params = []
    ) {}

    /**
     * Como estamos retornando uma string diretamente,
     * o Laravel não buscará por um arquivo .blade.php.
     */
    public function render()
    {
        return HookManager::render($this->name, $this->params);
    }
}
