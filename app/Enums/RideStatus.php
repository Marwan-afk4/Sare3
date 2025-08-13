<?php

namespace App\Enums;

enum RideStatus: string
{
    case Pending = 'pending';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Arrived = 'arrived';
    case WaitingUser = 'waiting_user';
    case Rejected = 'rejected';
    case Accepted = 'accepted';
    case Finshed = 'finshed';

    public static function labels(): array
    {
        return [
            self::Pending->value => __('Pending'),
            self::InProgress->value => __('In Progress'),
            self::Completed->value => __('Completed'),
            self::Cancelled->value => __('Cancelled'),
            self::Arrived->value => __('Arrived'),
            self::WaitingUser->value => __('Waiting User'),
            self::Rejected->value => __('Rejected'),
            self::Accepted->value => __('Accepted'),
            self::Finshed->value => __('Finished'),
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
            self::Pending => 'FBBF24', // yellow
            self::InProgress => '3B82F6', // blue
            self::Completed => '22C55E', // green
            self::Cancelled => 'EF4444', // red
            self::Arrived => 'FBBF24', // yellow
            self::WaitingUser => 'FBBF24', // yellow
            self::Rejected => 'EF4444', // red
            self::Accepted => '22C55E', // green
            self::Finshed => '22C55E', // green
        };
    }

    public function textColor(): string
    {
        return match ($this) {
            self::Pending => '000000', // black
            self::InProgress => 'FFFFFF', // white
            self::Completed => 'FFFFFF', // white
            self::Cancelled => 'FFFFFF', // white
            self::Arrived => '000000', // black
            self::WaitingUser => '000000', // black
            self::Rejected => 'FFFFFF', // white
            self::Accepted => 'FFFFFF', // white
            self::Finshed => 'FFFFFF', // white
        };
    }
}
