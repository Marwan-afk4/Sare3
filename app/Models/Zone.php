<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Zone extends Model
{
    use HasFactory;

    protected $table = 'zones';

    protected $fillable = [
        'name',
        'from_lat',
        'from_lng',
        'to_lat',
        'to_lng',
        'polygon_coordinates'
    ];

    protected $casts = [
        'polygon_coordinates' => 'array'
    ];

    public $timestamps = true;

    /**
     * Get the polygon coordinates attribute.
     * Ensures it's always returned as an array, even if stored inconsistently.
     */
    protected function polygonCoordinates(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                // If null or empty, return empty array
                if (empty($value)) {
                    return [];
                }
                
                // If it's already an array, return it
                if (is_array($value)) {
                    return $value;
                }
                
                // If it's a string, try to decode it
                if (is_string($value)) {
                    $decoded = json_decode($value, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        return $decoded;
                    }
                    // If JSON decode failed, return empty array
                    return [];
                }
                
                // For any other type, return empty array
                return [];
            },
            set: function ($value) {
                // If null or empty, store as null
                if (empty($value)) {
                    return null;
                }
                
                // If it's already a string (JSON), validate and return it
                if (is_string($value)) {
                    $decoded = json_decode($value, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        return $value;
                    }
                }
                
                // If it's an array, encode it
                if (is_array($value)) {
                    return json_encode($value);
                }
                
                return null;
            }
        );
    }


    public function carCategories()
    {
        return $this->belongsToMany(CarCategory::class, 'car_category_zone')
                    ->withPivot(['price_per_km', 'price_per_min', 'base_price', 'min_price'])
                    ->withTimestamps();
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function rides()
    {
        return $this->hasMany(Ride::class);
    }

}
