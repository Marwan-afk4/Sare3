<?php

namespace App\Enums;

enum OtpTypes: string
{

    case User = 'user';
    case Driver = 'driver';


    public static function labels(): array
    {
        return [
            self::User->value => __('User'),
            self::Driver->value => __('Driver'),
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

    public function badge(): string
    {
        $icon = match ($this) {
            self::User   => '<i class="fa fa-user me-1"></i>',
            self::Driver => '<i class="fa fa-car me-1"></i>',
        };

        $bgColor = match ($this) {
            self::User   => '0d6efd', // blue
            self::Driver => '198754', // green
        };

        $textColor = 'ffffff';

        return sprintf(
            '<span class="badge rounded-pill px-3 py-2" style="background-color: #%s; color: #%s;">%s%s</span>',
            $bgColor,
            $textColor,
            $icon,
            $this->label()
        );
    }
}
