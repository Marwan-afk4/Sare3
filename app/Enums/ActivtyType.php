<?php

namespace App\Enums;

enum ActivtyType :string
{
    case Active = 'active';
    case Inactive = 'inactive';

    public static function labels(): array
    {
        return [
            self::Active->value => __('Active'),
            self::Inactive->value => __('Inactive'),
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

    public function color(): string
    {
        return match ($this) {
            self::Inactive => 'EF4444',// red
            self::Active => '22C55E',// green
        };
    }

    public function textColor(): string
    {
        return match ($this) {
            self::Inactive => 'FFFFFF',// white
            self::Active => 'FFFFFF',// black
        };
    }
}
