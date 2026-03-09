<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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

    protected $appends = ['image_link'];

    public function getImageLinkAttribute()
    {
        $path = $this->image_path;
        if (! is_string($path) || $path === '' || str_contains($path, 'HTTP/') || str_contains($path, '{"errors"')) {
            return null;
        }
        if (Storage::disk('public')->exists($path)) {
            return asset('storage/' . $path);
        }
        return null;
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function documentType()
    {
        return $this->belongsTo(DocumentType::class);
    }
}
