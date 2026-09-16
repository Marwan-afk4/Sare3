<?php

namespace App\Enums;

enum VehicleType: string
{
    case Bike = 'bike';
    case Motorcycle = 'motorcycle';

    public static function labels(): array
    {
        return [
            self::Bike->value => __('Bike'),
            self::Motorcycle->value => __('Motorcycle'),
        ];
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return self::labels()[$this->value];
    }

    /**
     * Hardcoded required document images for each vehicle type.
     * Keys are used as the identifiers the mobile app must send back
     * inside the `documents` map on store-vehicle.
     */
    public static function requiredDocs(string $type): array
    {
        return match ($type) {
            self::Motorcycle->value => [
                ['key' => 'rider_image', 'name' => __('Rider photo')],
                ['key' => 'identity_image', 'name' => __('Identity photo')],
                ['key' => 'vehicle_image', 'name' => __('Motorcycle photo')],
                ['key' => 'license_image', 'name' => __('Motorcycle license')],
            ],
            self::Bike->value => [
                ['key' => 'rider_image', 'name' => __('Rider photo')],
                ['key' => 'identity_image', 'name' => __('Identity photo')],
                ['key' => 'vehicle_image', 'name' => __('Bike photo')],
            ],
            default => [],
        };
    }
}
