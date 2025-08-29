<?php

namespace App\View\Components;

use Illuminate\View\Component;

class FormSelect extends Component
{
    public string $name;
    public string $label;
    public array $options;

    public array $disabledOptions;
    public array|string|null $selected;   // ✅ يقبل string أو array
    public bool $required;
    public bool $disabled;
    public ?array $attrs;
    public bool $multiple;

    public function __construct(
        string $name,
        string $label,
        array $options = [],
        array $disabledOptions = [],
        array|string|null $selected = null,   // ✅
        bool $required = false,
        bool $disabled = false,
        bool $multiple = false,
        ?array $attrs = []
    ) {
        $this->name = $name;
        $this->label = $label;
        $this->options = $options;
        $this->disabledOptions = $disabledOptions;
        $this->selected = $selected;
        $this->required = $required;
        $this->disabled = $disabled;
        $this->multiple = $multiple;
        $this->attrs = $attrs;
    }

    public function render()
    {
        return view('components.form-select');
    }
}
