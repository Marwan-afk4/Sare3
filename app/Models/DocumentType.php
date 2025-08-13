<?php

namespace App\Models;

use App\Enums\ActiveStatuses;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;


class DocumentType extends Model
{
    use HasFactory;

    protected $table = 'document_types';

    protected $fillable = [
        'name',
        'is_required'
    ];

    public $timestamps = true;

    protected function isRequired(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                // If DB has 1 → treat as active
                if ($value === 1 || $value === '1') {
                    return ActiveStatuses::Active;
                }
                // If DB has 0 → treat as inactive
                if ($value === 0 || $value === '0') {
                    return ActiveStatuses::Inactive;
                }
                // If DB has 'active' or 'inactive', use enum directly
                if (in_array($value, ActiveStatuses::values(), true)) {
                    return ActiveStatuses::from($value);
                }
                // Default
                return ActiveStatuses::Active;
            },
            set: fn($value) => $value instanceof ActiveStatuses
                ? $value->value
                : $value
        );
    }

    public function driverDocuments()
    {
        return $this->hasMany(DriverDocument::class);
    }

}
