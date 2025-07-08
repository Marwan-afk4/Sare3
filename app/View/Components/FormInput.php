<?php

namespace App\View\Components;

use Illuminate\View\Component;

class FormInput extends Component
{
    public string $name;
    public string $label;

    public ?string $class;

    public ?string $divClass;

    public string $type;
    public ?string $value;
    public bool $required;

    public bool $disabled;
    public ?array $attrs;

    public function __construct(
        string $name,
        string $label,
        ?string $class,
        ?string $divClass,
        string $type = 'text',
        ?string $value = null,
        bool $required = false,
        bool $disabled = false,
        ?array $attrs = array()
    ) {
        $this->name = $name;
        $this->label = $label;
        $this->class = $class ?? 'form-control';
        $this->divClass = $divClass ?? 'form-floating mb-3';
        $this->type = $type;
        $this->value = $value;
        $this->required = $required;
        $this->disabled = $disabled;

        if (empty($attrs) || !array_key_exists('autocomplete', $attrs)) {
            $attrs['autocomplete'] = 'off';
        }

        $this->attrs = $attrs;
    }

    public function render()
    {
        return view('components.form-input');
    }
}

