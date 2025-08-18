<?php

namespace App\Models;

use App\Enums\OtpTypes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtpLimit extends Model
{
    use HasFactory;

    protected $table = 'otp_limits';

    protected $fillable = [
        'type',
        'otp_limit'
    ];

    protected $casts = [
        'type' => OtpTypes::class,
    ];

    public $timestamps = true;

}
