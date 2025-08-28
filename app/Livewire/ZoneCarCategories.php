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
        $this->validate([
            'car_category_id' => 'required|exists:car_categories,id',
            'base_price' => 'required|numeric|min:0',
            'price_per_km' => 'required|numeric|min:0',
            'price_per_min' => 'required|numeric|min:0',
        ]);

        // check if exists already
        $exists = $this->zoneCategories->firstWhere('id', $this->car_category_id);

        if ($exists) {
            $this->dispatch('categoryExists', name: $exists->name);
            return;
        }

        $this->zone->carCategories()->syncWithoutDetaching([
            $this->car_category_id => [
                'base_price'   => $this->base_price,
                'price_per_km' => $this->price_per_km,
                'price_per_min'=> $this->price_per_min,
            ]
        ]);


        $this->reset(['car_category_id', 'base_price', 'price_per_km', 'price_per_min']);
        $this->updateZoneCategories();
    }

    public function deleteCategory($carCategoryId)
    {
        $this->zone->carCategories()->detach($carCategoryId);
        $this->updateZoneCategories();
    }

    public function render()
    {
        $this->updateZoneCategories();
        return view('livewire.zone-car-categories');
    }
}
