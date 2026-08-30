<?php

namespace App\Models;

use App\Enums\PhoneVerificationMethod;
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

    /**
     * Max percentage of the ride fare that can be paid from the user wallet
     */
    public static function getUserWalletPaymentPercentage(): float
    {
        return (float) static::get('user_wallet_payment_percentage', 100);
    }

    /**
     * Set max percentage of the ride fare that can be paid from the user wallet
     */
    public static function setUserWalletPaymentPercentage(float $percentage): void
    {
        static::set(
            'user_wallet_payment_percentage',
            $percentage,
            'string',
            'Maximum percentage of the ride fare that can be paid from the user wallet (0-100%)'
        );
    }

    /**
     * Get referral discount percentage
     */
    public static function getReferralDiscountPercentage(): float
    {
        return (float) static::get('referral_discount_percentage', 10);
    }

    /**
     * Set referral discount percentage
     */
    public static function setReferralDiscountPercentage(float $percentage): void
    {
        static::set('referral_discount_percentage', $percentage, 'string', 'Discount percentage for referral users');
    }

    /**
     * Get referral discount rides count
     */
    public static function getReferralDiscountRides(): int
    {
        return (int) static::get('referral_discount_rides', 5);
    }

    /**
     * Set referral discount rides count
     */
    public static function setReferralDiscountRides(int $rides): void
    {
        static::set('referral_discount_rides', $rides, 'integer', 'Number of rides with referral discount');
    }

    /**
     * Get referrer reward percentage
     */
    public static function getReferrerRewardPercentage(): float
    {
        return (float) static::get('referrer_reward_percentage', 5);
    }

    /**
     * Set referrer reward percentage
     */
    public static function setReferrerRewardPercentage(float $percentage): void
    {
        static::set('referrer_reward_percentage', $percentage, 'string', 'Reward percentage for referrers');
    }

    /**
     * Get referrer reward rides count
     */
    public static function getReferrerRewardRides(): int
    {
        return (int) static::get('referrer_reward_rides', 10);
    }

    /**
     * Set referrer reward rides count
     */
    public static function setReferrerRewardRides(int $rides): void
    {
        static::set('referrer_reward_rides', $rides, 'integer', 'Number of rides with referrer rewards');
    }

    public static function getStoredPhoneVerificationMethod(): string
    {
        $method = static::get('phone_verification_method', PhoneVerificationMethod::BackendOtp->value);

        return in_array($method, PhoneVerificationMethod::values(), true)
            ? $method
            : PhoneVerificationMethod::BackendOtp->value;
    }

    public static function getPhoneVerificationMethod(): string
    {
        $method = PhoneVerificationMethod::tryFrom(static::getStoredPhoneVerificationMethod());

        return $method?->appValue() ?? PhoneVerificationMethod::BackendOtp->value;
    }

    public static function setPhoneVerificationMethod(string $method): void
    {
        static::set(
            'phone_verification_method',
            $method,
            'string',
            'Active phone verification method (backend_otp, kastana, or firebase_otp). Mobile apps still receive backend_otp when Kastana is selected.'
        );
    }

    public static function isSignupGiftEnabled(): bool
    {
        return (bool) static::get('signup_gift_enabled', false);
    }

    public static function setSignupGiftEnabled(bool $enabled): void
    {
        static::set(
            'signup_gift_enabled',
            $enabled,
            'boolean',
            'Automatically add a welcome gift to a user wallet on first signup'
        );
    }

    public static function getSignupGiftAmount(): float
    {
        return round((float) static::get('signup_gift_amount', 0), 2);
    }

    public static function setSignupGiftAmount(float $amount): void
    {
        static::set(
            'signup_gift_amount',
            round($amount, 2),
            'string',
            'Welcome gift amount added to the user wallet on first signup'
        );
    }
}