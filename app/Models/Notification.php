<?php

namespace App\Models;

use App\Enums\OtpTypes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications';

    protected $fillable = [
        'type',
        'title',
        'message',
        'driver_id',
        'user_id'
    ];

    public $timestamps = true;

    protected $hidden = [
        'updated_at',
        'created_at',
    ];

    protected $casts = [
        'type'=> OtpTypes::class, //user or driver
    ];


    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
