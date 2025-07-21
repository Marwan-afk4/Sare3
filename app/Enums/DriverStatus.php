<?php

namespace App\Enums;

enum DriverStatus: string
{
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Pending = 'pending'; // Optional, if you want to include a pending status

    public static function labels(): array
    {
        return [
            self::Approved->value => __('Approved'),
            self::Rejected->value => __('Rejected'),
            self::Pending->value => __('Pending'),
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
            self::Approved => '22C55E', // green
            self::Rejected => 'EF4444', // red
            self::Pending => 'FBBF24', // yellow
        };
    }

    public function textColor(): string
    {
        return match ($this) {
            self::Approved => 'FFFFFF', // white
            self::Rejected => 'FFFFFF', // white
            self::Pending => 'FFFFFF', // white
        };
    }
}
