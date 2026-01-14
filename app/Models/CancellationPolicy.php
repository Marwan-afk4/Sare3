<?php

namespace App\Models;

use App\Enums\ActiveStatuses;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CancellationPolicy extends Model
{
    use HasFactory;

    protected $table = 'cancellation_policies';

    protected $fillable = [
        'name',
        'user_type',
        'zone_id',
        'time_limit_minutes',
        'penalty_amount',
        'penalty_percent',
        'description',
        'status'
    ];

    public $timestamps = true;

    protected $casts = [
        'status' => ActiveStatuses::class
    ];


    public function cancelationRides()
    {
        return $this->hasMany(CancelationRide::class);
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }
}
