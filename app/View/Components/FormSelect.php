<?php
namespace App\View\Components;

use Illuminate\View\Component;

class FormSelect extends Component
{
    public string $name;
    public string $label;
    public array $options;

    public ?array $disabledOptions;
    public ?string $selected;
    public bool $required;



    public function __construct(
        string $name,
        string $label,
        array $options = [],
        array $disabledOptions = [],
        $selected = null,
        bool $required = false
    ) {
        $this->name = $name;
        $this->label = $label;
        $this->options = $options;
        $this->disabledOptions = $disabledOptions;
        $this->selected = $selected;
        $this->required = $required;
    }

    public function render()
    {
        return view('components.form-select');
    }
}
