<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Point extends Model
{
    use HasFactory;

    protected $table = 'points';

    protected $fillable = [
        'user_id',
        'point_type',
        'latitude',
        'longitude',
        'location'
    ];
    
    public $timestamps = true;

    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
