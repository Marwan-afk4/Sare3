<?php

namespace App\Livewire;

use App\Models\CarCategory;
use App\Models\Zone;
use Livewire\Component;

class ZoneCarCategories extends Component
{

    public Zone $zone;

    public $car_category_id = '';
    public $base_price = '';
    public $price_per_km = '';
    public $price_per_min = '';
    public $min_price = '';

    public ?int $editingCategoryId = null;

    public array $carCategories = [];
    public $zoneCategories;

    public function mount(Zone $zone)
    {
        $this->zone = $zone;
        $this->carCategories = CarCategory::pluck('name', 'id')->toArray();
        $this->updateZoneCategories();
    }

    private function updateZoneCategories()
    {
        $this->zone->load('carCategories');
        $this->zoneCategories = $this->zone->carCategories;
    }


    public function saveCategory()
    {
        if ($this->editingCategoryId) {
            $this->car_category_id = (string) $this->editingCategoryId;
        }

        $this->validate([
            'car_category_id' => 'required|exists:car_categories,id',
            'base_price' => 'required|numeric|min:0',
            'price_per_km' => 'required|numeric|min:0',
            'price_per_min' => 'required|numeric|min:0',
            'min_price' => 'required|numeric|min:0',
        ]);

        $prices = [
            'base_price' => $this->base_price,
            'price_per_km' => $this->price_per_km,
            'price_per_min' => $this->price_per_min,
            'min_price' => $this->min_price,
        ];

        if ($this->editingCategoryId) {
            $this->zone->carCategories()->updateExistingPivot($this->editingCategoryId, $prices);
            $this->cancelEdit();
            $this->updateZoneCategories();

            return;
        }

        $exists = $this->zoneCategories->firstWhere('id', $this->car_category_id);

        if ($exists) {
            $this->dispatch('categoryExists', name: $exists->name);
            return;
        }

        $this->zone->carCategories()->syncWithoutDetaching([
            $this->car_category_id => $prices,
        ]);

        $this->reset(['car_category_id', 'base_price', 'price_per_km', 'price_per_min', 'min_price']);
        $this->updateZoneCategories();
    }

    public function editCategory($carCategoryId)
    {
        $this->updateZoneCategories();

        $category = $this->zoneCategories->firstWhere('id', (int) $carCategoryId);

        if (! $category) {
            return;
        }

        $this->editingCategoryId = (int) $category->id;
        $this->car_category_id = (string) $category->id;
        $this->base_price = (string) $category->pivot->base_price;
        $this->price_per_km = (string) $category->pivot->price_per_km;
        $this->price_per_min = (string) $category->pivot->price_per_min;
        $this->min_price = (string) ($category->pivot->min_price ?? '');
        $this->resetValidation();
    }

    public function cancelEdit()
    {
        $this->editingCategoryId = null;
        $this->reset(['car_category_id', 'base_price', 'price_per_km', 'price_per_min', 'min_price']);
        $this->resetValidation();
    }

    public function deleteCategory($carCategoryId)
    {
        $this->zone->carCategories()->detach($carCategoryId);

        if ((int) $this->editingCategoryId === (int) $carCategoryId) {
            $this->cancelEdit();
        }

        $this->updateZoneCategories();
    }

    public function render()
    {
        $this->updateZoneCategories();
        return view('livewire.zone-car-categories');
    }
}
