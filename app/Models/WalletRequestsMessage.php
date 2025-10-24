<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletRequestsMessage extends Model
{
    use HasFactory;

    protected $table = 'wallet_requests_messages';

    protected $fillable = [
        'wallet_request_id',
        'admin_id',
        'driver_id',
        'admin_message',
        'driver_message'
    ];

    public $timestamps = true;


    public function walletRequest()
    {
        return $this->belongsTo(WalletRequest::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

}
