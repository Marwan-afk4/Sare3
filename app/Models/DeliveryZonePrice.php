<?php

namespace App\Models;

use App\Enums\VehicleType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryZonePrice extends Model
{
    use HasFactory;

    protected $table = 'delivery_zone_prices';

    protected $fillable = [
        'zone_id',
        'vehicle_type',
        'base_price',
        'price_per_km',
        'price_per_min',
        'min_price',
    ];

    protected $casts = [
        'vehicle_type' => VehicleType::class,
        'base_price' => 'decimal:2',
        'price_per_km' => 'decimal:2',
        'price_per_min' => 'decimal:2',
        'min_price' => 'decimal:2',
    ];

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    /**
     * The single delivery price an admin set for this zone.
     * Bike and motorcycle share it; if older rows differ, the latest edit wins.
     */
    public static function forZone(int|string $zoneId): ?self
    {
        return static::where('zone_id', $zoneId)->orderByDesc('updated_at')->first();
    }

    /**
     * Calculate a price for the given distance/time using this row's rates,
     * floored by min_price.
     */
    public function calculatePrice(float $km, float $minutes): float
    {
        $price = (float) $this->base_price
            + ($km * (float) $this->price_per_km)
            + ($minutes * (float) $this->price_per_min);

        return max($price, (float) $this->min_price);
    }
}
