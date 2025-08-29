<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RideProfit extends Model
{
    use HasFactory;

    protected $fillable = [
        'ride_id',
        'driver_id',
        'total_fare',
        'admin_profit_percentage',
        'admin_profit_amount',
        'driver_amount',
        'processed_at'
    ];

    protected $casts = [
        'total_fare' => 'decimal:2',
        'admin_profit_percentage' => 'decimal:2',
        'admin_profit_amount' => 'decimal:2',
        'driver_amount' => 'decimal:2',
        'processed_at' => 'datetime'
    ];

    public function ride()
    {
        return $this->belongsTo(Ride::class);
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    /**
     * Calculate profit amounts based on fare and percentage
     */
    public static function calculateProfit(float $totalFare, float $profitPercentage): array
    {
        $adminProfitAmount = round($totalFare * ($profitPercentage / 100), 2);
        $driverAmount = round($totalFare - $adminProfitAmount, 2);

        return [
            'admin_profit_amount' => $adminProfitAmount,
            'driver_amount' => $driverAmount
        ];
    }

    /**
     * Create profit record for a ride
     */
    public static function createForRide(Ride $ride, float $totalFare, float $profitPercentage): self
    {
        $amounts = self::calculateProfit($totalFare, $profitPercentage);

        return self::create([
            'ride_id' => $ride->id,
            'driver_id' => $ride->driver_id,
            'total_fare' => $totalFare,
            'admin_profit_percentage' => $profitPercentage,
            'admin_profit_amount' => $amounts['admin_profit_amount'],
            'driver_amount' => $amounts['driver_amount'],
            'processed_at' => now()
        ]);
    }
}