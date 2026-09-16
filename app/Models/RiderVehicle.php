<?php

namespace App\Models;

use App\Enums\VehicleType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiderVehicle extends Model
{
    use HasFactory;

    protected $table = 'rider_vehicles';

    protected $fillable = [
        'rider_id',
        'type',
        'rider_image',
        'identity_image',
        'vehicle_image',
        'license_image',
    ];

    protected $casts = [
        'type' => VehicleType::class,
    ];

    protected $appends = [
        'rider_image_link',
        'identity_image_link',
        'vehicle_image_link',
        'license_image_link',
    ];

    public function getRiderImageLinkAttribute()
    {
        return $this->rider_image ? asset('storage/' . $this->rider_image) : null;
    }

    public function getIdentityImageLinkAttribute()
    {
        return $this->identity_image ? asset('storage/' . $this->identity_image) : null;
    }

    public function getVehicleImageLinkAttribute()
    {
        return $this->vehicle_image ? asset('storage/' . $this->vehicle_image) : null;
    }

    public function getLicenseImageLinkAttribute()
    {
        return $this->license_image ? asset('storage/' . $this->license_image) : null;
    }

    public function rider()
    {
        return $this->belongsTo(User::class, 'rider_id');
    }
}
