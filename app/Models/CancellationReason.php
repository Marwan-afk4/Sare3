<?php

namespace App\Models;

use App\Enums\OtpTypes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CancellationReason extends Model
{
    use HasFactory;

    protected $table = 'cancellation_reasons';

    protected $fillable = [
        'reason',
        'type',
        'is_active'
    ];

    public $timestamps = true;

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'type' => OtpTypes::class
    ];

    public function rides()
    {
        return $this->hasMany(Ride::class);
    }
}
