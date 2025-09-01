<?php

namespace App\Services;

use App\Models\User;
use App\Models\Ride;
use App\Services\ReferralDiscountService;

class RideService
{
    protected ReferralDiscountService $referralDiscountService;

    public function __construct(ReferralDiscountService $referralDiscountService)
    {
        $this->referralDiscountService = $referralDiscountService;
    }

    /**
     * Calculate ride cost with potential referral discount
     */
    public function calculateRideCost(User $user, float $baseCost): array
    {
        $discountPreview = $this->referralDiscountService->getDiscountPreview($user, $baseCost);
        
        return [
            'base_cost' => $baseCost,
            'final_cost' => $discountPreview['final_cost'],
            'total_discount_amount' => $discountPreview['total_discount_amount'],
            'applied_discounts' => $discountPreview['applied_discounts']
        ];
    }

    /**
     * Complete ride with discount application
     */
    public function completeRideWithDiscount(Ride $ride, array $costData): void
    {
        // The discounts are already applied in calculateRideCost and stored in database
        // Just update the final ride amount if needed
        if (isset($costData['final_fare'])) {
            $ride->update(['total_amount' => $costData['final_fare']]);
        }
    }

    /**
     * Get ride estimate with discount preview
     */
    public function getRideEstimateWithDiscount(User $user, float $estimatedCost): array
    {
        $discountPreview = $this->referralDiscountService->getDiscountPreview($user, $estimatedCost);
        
        return [
            'estimated_cost' => $estimatedCost,
            'discount_preview' => $discountPreview
        ];
    }
}