<?php

namespace App\Models;

use App\Enums\DriverStatus;
use App\Enums\WalletRequestType;
use App\Observers\WalletRequestObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// #[ObservedBy(WalletRequestObserver::class)]
class WalletRequest extends Model
{
    use HasFactory;

    protected $table = 'wallet_requests';

    protected $fillable = [
        'driver_id',
        'amount',
        'type',
        'status',
        'note'
    ];

    protected $casts = [
        'status' => DriverStatus::class,
        'type' => WalletRequestType::class,
    ];

    public $timestamps = true;


    public function driver()
    {
        return $this->belongsTo(User::class);
    }

    public function messages()
    {
        return $this->hasMany(WalletRequestsMessage::class)->orderBy('created_at', 'asc');
    }

}
