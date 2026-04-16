<?php

namespace App\View\Components;

use Illuminate\View\Component;

class UploadArea extends Component
{
    public $name;
    public $id;
    public $label;
    public $buttonLabel;
    public $message;
    public $valueMessage;
    public $clearButton;
    public $required;
    public $accept;

    public function __construct(
        $name = 'fileup',
        $id = null,
        $label = 'Upload de arquivo',
        $buttonLabel = 'Escolher arquivo',
        $message = 'Solte arquivos aqui para fazer upload',
        $valueMessage = 'Arquivo selecionado: %s',
        $clearButton = true,
        $required = false,
        $accept = null
    ) {
        $this->name = $name;
        $this->id = $id;
        $this->label = $label;
        $this->buttonLabel = $buttonLabel;
        $this->message = $message;
        $this->valueMessage = $valueMessage;
        $this->clearButton = $clearButton;
        $this->required = $required;
        $this->accept = $accept;
    }

    public function render()
    {
        return view('components.upload-area');
    }
}
