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
        'document_type_id',
        'image_path',
    ];

    public $timestamps = true;


    public function driver()
    {
        return $this->belongsTo(User::class,'driver_id');
    }

    public function documentType()
    {
        return $this->belongsTo(DocumentType::class);
    }

}
