<?php

namespace App\Livewire;

use App\Models\Coupon;
use Livewire\Component;
use Livewire\WithPagination;

class CouponManager extends Component
{
    use WithPagination;

    public $showModal = false;
    public $editMode = false;
    public $couponId;
    
    // Form fields
    public $code = '';
    public $name = '';
    public $description = '';
    public $type = 'percentage';
    public $value = '';
    public $minimum_ride_amount = '';
    public $maximum_discount = '';
    public $usage_limit = '';
    public $user_usage_limit = 1;
    public $is_active = true;
    public $starts_at = '';
    public $expires_at = '';
    
    // Filters
    public $search = '';
    public $statusFilter = '';

    protected $rules = [
        'code' => 'required|string|max:50',
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'type' => 'required|in:percentage,fixed',
        'value' => 'required|numeric|min:0',
        'minimum_ride_amount' => 'nullable|numeric|min:0',
        'maximum_discount' => 'nullable|numeric|min:0',
        'usage_limit' => 'nullable|integer|min:1',
        'user_usage_limit' => 'required|integer|min:1',
        'is_active' => 'boolean',
        'starts_at' => 'required|date',
        'expires_at' => 'required|date|after:starts_at',
    ];

    public function mount()
    {
        $this->starts_at = now()->format('Y-m-d\TH:i');
        $this->expires_at = now()->addDays(30)->format('Y-m-d\TH:i');
    }

    public function render()
    {
        $query = Coupon::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('code', 'like', '%' . $this->search . '%')
                  ->orWhere('name', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->statusFilter === 'active') {
            $query->active();
        } elseif ($this->statusFilter === 'inactive') {
            $query->where('is_active', false);
        } elseif ($this->statusFilter === 'expired') {
            $query->where('expires_at', '<', now());
        }

        $coupons = $query->withCount('usages')
                        ->orderBy('created_at', 'desc')
                        ->paginate(10);

        return view('livewire.coupon-manager', compact('coupons'));
    }

    public function openModal()
    {
        $this->resetForm();
        $this->showModal = true;
        $this->editMode = false;
    }

    public function editCoupon($id)
    {
        $coupon = Coupon::findOrFail($id);
        
        $this->couponId = $coupon->id;
        $this->code = $coupon->code;
        $this->name = $coupon->name;
        $this->description = $coupon->description;
        $this->type = $coupon->type;
        $this->value = $coupon->value;
        $this->minimum_ride_amount = $coupon->minimum_ride_amount;
        $this->maximum_discount = $coupon->maximum_discount;
        $this->usage_limit = $coupon->usage_limit;
        $this->user_usage_limit = $coupon->user_usage_limit;
        $this->is_active = $coupon->is_active;
        $this->starts_at = $coupon->starts_at->format('Y-m-d\TH:i');
        $this->expires_at = $coupon->expires_at->format('Y-m-d\TH:i');
        
        $this->showModal = true;
        $this->editMode = true;
    }

    public function saveCoupon()
    {
        $this->validate();

        // Additional validation for percentage
        if ($this->type === 'percentage' && $this->value > 100) {
            $this->addError('value', 'Percentage value cannot exceed 100%');
            return;
        }

        $data = [
            'code' => strtoupper($this->code),
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'value' => $this->value,
            'minimum_ride_amount' => $this->minimum_ride_amount ?: null,
            'maximum_discount' => $this->maximum_discount ?: null,
            'usage_limit' => $this->usage_limit ?: null,
            'user_usage_limit' => $this->user_usage_limit,
            'is_active' => $this->is_active,
            'starts_at' => $this->starts_at,
            'expires_at' => $this->expires_at,
        ];

        if ($this->editMode) {
            $coupon = Coupon::findOrFail($this->couponId);
            $coupon->update($data);
            session()->flash('message', 'Coupon updated successfully!');
        } else {
            Coupon::create($data);
            session()->flash('message', 'Coupon created successfully!');
        }

        $this->closeModal();
    }

    public function deleteCoupon($id)
    {
        $coupon = Coupon::findOrFail($id);
        
        if ($coupon->usages()->exists()) {
            session()->flash('error', 'Cannot delete coupon that has been used.');
            return;
        }

        $coupon->delete();
        session()->flash('message', 'Coupon deleted successfully!');
    }

    public function toggleStatus($id)
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->update(['is_active' => !$coupon->is_active]);
        
        session()->flash('message', 'Coupon status updated successfully!');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->reset([
            'couponId', 'code', 'name', 'description', 'type', 'value',
            'minimum_ride_amount', 'maximum_discount', 'usage_limit',
            'user_usage_limit', 'is_active'
        ]);
        
        $this->starts_at = now()->format('Y-m-d\TH:i');
        $this->expires_at = now()->addDays(30)->format('Y-m-d\TH:i');
        $this->user_usage_limit = 1;
        $this->is_active = true;
        $this->type = 'percentage';
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }
}