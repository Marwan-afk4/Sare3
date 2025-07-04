<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavedCard extends Model
{
    use HasFactory;

    protected $table = 'saved_cards';

    protected $fillable = [
        'user_id',
        'card_token',
        'last_four',
        'expiry_month',
        'expiry_year',
        'transaction_reference'
    ];
    
    public $timestamps = true;

    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
