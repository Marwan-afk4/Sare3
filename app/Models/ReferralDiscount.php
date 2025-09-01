<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralDiscount extends Model
{
    use HasFactory;

    protected $fillable = [
        'referral_id',
        'ride_id',
        'original_amount',
        'discount_amount',
        'final_amount',
        'discount_percentage'
    ];

    protected $casts = [
        'original_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'discount_percentage' => 'decimal:2'
    ];

    public function referral()
    {
        return $this->belongsTo(Referral::class);
    }

    public function ride()
    {
        return $this->belongsTo(Ride::class);
    }
}