<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtpLimit extends Model
{
    use HasFactory;

    protected $table = 'otp_limits';

    protected $fillable = [
        'user_id',
        'driver_id',
        'otp_limit',
        'otp_used'
    ];

    public $timestamps = true;


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

}
