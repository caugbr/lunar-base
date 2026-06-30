<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Models\Page;

class PagePicker extends Component
{
    public string $orderBy;
    public string $valueField;
    public string $labelField;
    public bool $showNamespace;
    public $status;
    public $selected;
    public $exclude;
    public string $placeholder;
    public $pages;

    public function __construct(
        $selected = null,
        $exclude = null,
        string $orderBy = 'title',
        string $valueField = 'id',
        string $labelField = 'title',
        $status = 'published',
        string $placeholder = '-- Selecione uma página --',
        bool $showNamespace = false
    ) {
        $this->selected = $selected;
        $this->exclude = $exclude;
        $this->orderBy = $orderBy;
        $this->valueField = $valueField;
        $this->labelField = $labelField;
        $this->status = $status;
        $this->placeholder = $placeholder;
        $this->showNamespace = $showNamespace;

        $columns = [$this->valueField, $this->labelField, $this->orderBy];
        if ($this->showNamespace) {
            $columns[] = 'namespace';
        }
        $columns = array_unique($columns);

        $query = Page::select($columns);

        // Se houver um valor a ser excluído
        if ($this->exclude !== null) {
            $query->where($this->valueField, '!=', $this->exclude);
        }

        // Filtro condicional de status
        if ($this->status !== 'all' && $this->status !== 'any' && $this->status !== null) {
            if (is_array($this->status)) {
                $query->whereIn('status', $this->status);
            } else {
                $query->where('status', $this->status);
            }
        }

        $this->pages = $query->orderBy($this->orderBy)->get();
    }

    public function render()
    {
        return view('components.page-picker');
    }
}
