<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class Search extends Component
{
    public array $types;
    public string $placeholder;
    public string $action;

    /**
     * @param array|string|null $types             Tipos permitidos (sobrescreve settings)
     * @param array|string|null $publicationTypes  Alias para $types
     * @param string            $placeholder       Texto do input
     * @param string|null       $action            URL de destino da busca
     */
    public function __construct(
        array|string|null $types = null,
        array|string|null $publicationTypes = null,
        string $placeholder = 'O que você procura?',
        ?string $action = null
    ) {
        $rawTypes = $types ?? $publicationTypes;

        if (is_string($rawTypes)) {
            $this->types = array_filter(array_map('trim', explode(',', $rawTypes)));
        } elseif (is_array($rawTypes)) {
            $this->types = $rawTypes;
        } else {
            $this->types = [];
        }

        $this->placeholder = $placeholder;
        $this->action = $action ?? route('search');
    }

    public function render(): View
    {
        return view('components.search');
    }
}
