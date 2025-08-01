<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RideRequestTimeLimit extends Model
{
    use HasFactory;

    protected $table = 'ride_request_time_limits';

    protected $fillable = [
        'time_limit_seconds'
    ];

    public $timestamps = true;

    protected $hidden = [
        'created_at',
        'updated_at',
    ];


}
