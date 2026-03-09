<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverBonusGrant extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'amount',
        'type',
        'bonus_tier_id',
        'granted_by',
        'note',
        'rides_count',
        'period_label',
    ];

    protected $casts = [
        'amount' => 'float',
        'rides_count' => 'integer',
    ];

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function tier()
    {
        return $this->belongsTo(BonusTier::class, 'bonus_tier_id');
    }

    public function grantedByAdmin()
    {
        return $this->belongsTo(User::class, 'granted_by');
    }
}
