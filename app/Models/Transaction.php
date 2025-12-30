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


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    /**
     * Append timezone-formatted timestamps to JSON
     */
    protected $appends = ['created_at_formatted', 'updated_at_formatted'];

    /**
     * Get formatted created_at in user's timezone
     */
    public function getCreatedAtFormattedAttribute()
    {
        if (!$this->created_at) {
            return null;
        }

        $user = $this->user ?? $this->driver;
        if ($user) {
            $timezone = $user->getTimezone();
            return $this->created_at->timezone($timezone)->format('Y-m-d H:i:s');
        }

        return $this->created_at->format('Y-m-d H:i:s');
    }

    /**
     * Get formatted updated_at in user's timezone
     */
    public function getUpdatedAtFormattedAttribute()
    {
        if (!$this->updated_at) {
            return null;
        }

        $user = $this->user ?? $this->driver;
        if ($user) {
            $timezone = $user->getTimezone();
            return $this->updated_at->timezone($timezone)->format('Y-m-d H:i:s');
        }

        return $this->updated_at->format('Y-m-d H:i:s');
    }

}
