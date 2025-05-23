<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverDocument extends Model
{
    use HasFactory;

    protected $table = 'driver_documents';

    protected $fillable = [
        'driver_id',
        'identity_number',
        'selfi_image',
        'face_identity',
        'back_identity',
        'driving_license'
    ];

    public $timestamps = true;


    public function driver()
    {
        return $this->belongsTo(User::class);
    }

}
