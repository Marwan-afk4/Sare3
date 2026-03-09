<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BonusTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'rides_count',
        'bonus_amount',
        'period_type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'bonus_amount' => 'float',
        'rides_count' => 'integer',
    ];

    public function grants()
    {
        return $this->hasMany(DriverBonusGrant::class);
    }

    /**
     * Get all active tiers ordered by rides_count ascending
     */
    public static function activeTiers(string $periodType = null)
    {
        $query = static::where('is_active', true)->orderBy('rides_count', 'asc');
        if ($periodType) {
            $query->where('period_type', $periodType);
        }
        return $query->get();
    }
}
