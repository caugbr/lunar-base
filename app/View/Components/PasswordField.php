<?php

namespace App\View\Components;

use Illuminate\View\Component;

class PasswordField extends Component
{
    public $name;
    public $label;
    public $id;
    public $required;
    public $confirm; // se true, mostra campo de confirmação

    public function __construct($name, $label = null, $id = null, $required = false, $confirm = false)
    {
        $this->name = $name;
        $this->label = $label ?? ucfirst(str_replace('_', ' ', $name));
        $this->id = $id ?? $name;
        $this->required = $required;
        $this->confirm = $confirm;
    }

    public function render()
    {
        return view('components.password-field');
    }
}
