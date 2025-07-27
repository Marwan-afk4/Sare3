<?php

namespace App\Enums;

enum WalletRequestType: string
{
    case Withdraw = 'withdraw';
    case Deposit = 'deposit';

    public static function labels(): array
    {
        return [
            self::Withdraw->value => __('Withdraw'),
            self::Deposit->value => __('Deposit'),
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
            self::Withdraw => '3B82F6', // blue
            self::Deposit => '22C55E', // green
        };
    }

    public function textColor(): string
    {
        return match ($this) {
            self::Withdraw => 'FFFFFF', // white
            self::Deposit => 'FFFFFF', // white
        };
    }
}
