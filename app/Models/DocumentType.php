<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentType extends Model
{
    use HasFactory;

    protected $table = 'document_types';

    protected $fillable = [
        'name',
        'is_required'
    ];

    public $timestamps = true;

    public function driverDocuments()
    {
        return $this->hasMany(DriverDocument::class);
    }
    
}
