<?php

namespace App\Livewire;
use Livewire\Component;
use App\Models\Supplier;

class SupplierAutocomplete extends Component
{
    public string $query = '';
    public array $suppliers = [];
    public ?int $selectedSupplierId = null;
    public string $selectedSupplierName = '';
    public bool $showError = false;

    public function updatedQuery($value)
    {
        // Reset selection if name is changed manually
        if ($this->selectedSupplierName !== $value) {
            $this->selectedSupplierId = null;
            $this->selectedSupplierName = '';
            $this->showError = false;
        }

        // Fetch matches
        $this->suppliers = strlen($value) >= 2
            ? Supplier::where('name', 'like', "%{$value}%")->limit(5)->get()->toArray()
            : [];
    }

    public function selectSupplier($id)
    {
        $supplier = Supplier::find($id);
        if ($supplier) {
            $this->selectedSupplierId = $supplier->id;
            $this->selectedSupplierName = $supplier->name;
            $this->query = $supplier->name;
            $this->suppliers = [];
            $this->showError = false;
        }
    }

    public function validateSupplierOnBlur()
    {
        if (empty($this->query)) {
            $this->selectedSupplierId = null;
            $this->selectedSupplierName = '';
            $this->showError = false;
            return;
        }

        if (!$this->selectedSupplierId || $this->query !== $this->selectedSupplierName) {
            $this->query = '';
            $this->selectedSupplierId = null;
            $this->selectedSupplierName = '';
            $this->showError = true;
        }
    }

    public function render()
    {
        return view('livewire.supplier-autocomplete');
    }
}
