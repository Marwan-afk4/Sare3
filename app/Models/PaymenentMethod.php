<?php

namespace App\Models;

use App\Enums\ActivtyType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymenentMethod extends Model
{
    use HasFactory;

    protected $table = 'paymenent_methods';

    protected $fillable = [
        'name',
        'status'
    ];

    public $timestamps = true;

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public $casts = [
        'status' => ActivtyType::class,
    ];

    public function rides()
    {
        return $this->hasMany(Ride::class, 'payment_method_id');
    }
}
