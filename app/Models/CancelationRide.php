<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CancelationRide extends Model
{
    use HasFactory;

    protected $table = 'cancelation_rides';

    protected $fillable = [
        'ride_id',
        'user_id',
        'driver_id',
        'cancelation_policy_id',
        'canceled_by',
        'penalty_applied',
        'penalty_amount',
        'is_refundable',
        'refund_amount',
        'canceled_at',
        'reason'
    ];

    public $timestamps = true;


    public function ride()
    {
        return $this->belongsTo(Ride::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function cancelationPolicy()
    {
        return $this->belongsTo(CancellationPolicy::class);
    }

}
