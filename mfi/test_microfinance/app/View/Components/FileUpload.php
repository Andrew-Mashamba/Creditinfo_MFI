<?php

namespace App\View\Components;

use Illuminate\View\Component;

class FileUpload extends Component
{
    public $field;
    public $label;
    public $multiple;
    public $accept;

    public function __construct($field, $label, $multiple = false, $accept = null)
    {
        $this->field = $field;
        $this->label = $label;
        $this->multiple = $multiple;
        $this->accept = $accept;
    }

    public function render()
    {
        return view('components.file-upload');
    }
} 