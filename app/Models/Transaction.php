<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transactions';

    protected $fillable = [
        'user_id',
        'driver_id',
        'amount',
        'description'
    ];

    public $timestamps = true;

    protected $appends = ['type'];

    /**
     * Get the transaction type based on amount
     */
    public function getTypeAttribute(): string
    {
        return $this->amount >= 0 ? 'topup' : 'deduction';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

}
