<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;

    protected $table = 'ratings';

    protected $fillable = [
        'rater_id',
        'ratee_id',
        'rate',
        'comment',
        'ratee_type'
    ];

    public $timestamps = true;


    public function rater()
    {
        return $this->belongsTo(User::class);
    }

    public function ratee()
    {
        return $this->belongsTo(User::class);
    }

}
