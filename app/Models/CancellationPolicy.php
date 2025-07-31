<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CancellationPolicy extends Model
{
    use HasFactory;

    protected $table = 'cancellation_policies';

    protected $fillable = [
        'name',
        'min_minutes',
        'max_minutes',
        'penalty_amount',
        'penalty_percent',
        'is_refundable',
        'description',
        'status'
    ];

    public $timestamps = true;


    public function cancelationRides()
    {
        return $this->hasMany(CancelationRide::class);
    }
}
