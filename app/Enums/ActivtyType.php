<?php

namespace App\Enums;

enum ActivtyType :string
{
    case Active = 'active';
    case InProgress = 'in_progress';
    case Rejected = 'rejected';
    case Inactive = 'inactive';

    public static function labels(): array
    {
        return [
            self::Active->value => __('Active'),
            self::InProgress->value => __('In Progress'),
            self::Inactive->value => __('Inactive'),
            self::Rejected->value => __('Rejected'),
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
            self::InProgress => 'FBBF24',//yellow
            self::Rejected => 'EF4444',// red
        };
    }

    public function textColor(): string
    {
        return match ($this) {
            self::Inactive => 'FFFFFF',// white
            self::Active => 'FFFFFF',// white
            self::InProgress => 'FFFFFF',// white
            self::Rejected => 'FFFFFF',// white
        };
    }
}
