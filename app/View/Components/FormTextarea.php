<?php

namespace App\View\Components;

use Illuminate\View\Component;

class FormTextarea extends Component
{
    public string $name;
    public string $label;
    public ?string $value;
    public bool $required;
    public ?array $attrs;

    public function __construct(
        string $name,
        string $label,
        ?string $value = null,
        bool $required = false,
        ?array $attrs = array()
    ) {
        $this->name = $name;
        $this->label = $label;
        $this->value = $value;
        $this->required = $required;
        $this->attrs = $attrs;        
    }

    public function render()
    {
        return view('components.form-textarea');
    }
}

