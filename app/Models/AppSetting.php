<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'description'
    ];

    /**
     * Get a setting value by key
     */
    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        
        if (!$setting) {
            return $default;
        }

        return static::castValue($setting->value, $setting->type);
    }

    /**
     * Set a setting value
     */
    public static function set(string $key, $value, string $type = 'string', string $description = null): void
    {
        static::updateOrCreate(
            ['key' => $key],
            [
                'value' => static::prepareValue($value, $type),
                'type' => $type,
                'description' => $description
            ]
        );
    }

    /**
     * Cast value based on type
     */
    protected static function castValue($value, string $type)
    {
        switch ($type) {
            case 'boolean':
                return $value === '1' || $value === 1 || $value === true || $value === 'true';
            case 'integer':
                return (int) $value;
            case 'json':
                return json_decode($value, true);
            default:
                return $value;
        }
    }

    /**
     * Prepare value for storage
     */
    protected static function prepareValue($value, string $type): string
    {
        switch ($type) {
            case 'boolean':
                return $value ? '1' : '0';
            case 'json':
                return json_encode($value);
            default:
                return (string) $value;
        }
    }

    /**
     * Check if ride verification is enabled
     */
    public static function isRideVerificationEnabled(): bool
    {
        return static::get('ride_verification_enabled', false);
    }

    /**
     * Get admin profit percentage
     */
    public static function getAdminProfitPercentage(): float
    {
        return (float) static::get('admin_profit_percentage', 0);
    }

    /**
     * Set admin profit percentage
     */
    public static function setAdminProfitPercentage(float $percentage): void
    {
        static::set('admin_profit_percentage', $percentage, 'string', 'Admin profit percentage from rides');
    }

    /**
     * Get minimum driver wallet balance
     */
    public static function getMinimumDriverWalletBalance(): float
    {
        return (float) static::get('minimum_driver_wallet_balance', 0);
    }

    /**
     * Set minimum driver wallet balance
     */
    public static function setMinimumDriverWalletBalance(float $amount): void
    {
        static::set('minimum_driver_wallet_balance', $amount, 'string', 'Minimum wallet balance required for drivers to go online');
    }
}