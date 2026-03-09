<?php

namespace App\Services;

use App\Helpers\FcmHelper;
use App\Models\BonusTier;
use App\Models\DriverBonusGrant;
use App\Models\Notification;
use App\Models\Ride;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WalletRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BonusService
{
    /**
     * Called after a driver completes a ride.
     * Checks if the driver has crossed any bonus tier milestones and grants them.
     */
    public function checkAndAwardTierBonus(User $driver, ?int $overrideRidesCount = null): array
    {
        $awarded = [];

        $tiers = BonusTier::where('is_active', true)->orderBy('rides_count', 'asc')->get();
        if ($tiers->isEmpty()) {
            return $awarded;
        }

        foreach ($tiers->groupBy('period_type') as $periodType => $periodTiers) {
            $ridesCount = $overrideRidesCount ?? $this->getDriverRidesCount($driver->id, $periodType);
            $periodLabel = $this->getPeriodLabel($periodType);

            foreach ($periodTiers as $tier) {
                if ($ridesCount < $tier->rides_count) {
                    continue;
                }

                // Avoid granting the same tier twice in the same period
                $alreadyGranted = DriverBonusGrant::where('driver_id', $driver->id)
                    ->where('bonus_tier_id', $tier->id)
                    ->where('type', 'tier')
                    ->where('period_label', $periodLabel)
                    ->exists();

                if ($alreadyGranted) {
                    continue;
                }

                // Grant the bonus
                $grant = $this->creditBonusToWallet(
                    driver: $driver,
                    amount: $tier->bonus_amount,
                    type: 'tier',
                    tierId: $tier->id,
                    grantedBy: null,
                    note: "Milestone bonus: {$tier->rides_count} rides ({$periodType})",
                    ridesCount: $ridesCount,
                    periodLabel: $periodLabel,
                );

                $awarded[] = $grant;

                // Notify driver
                $this->notifyDriver(
                    driver: $driver,
                    title: 'Bonus Earned!',
                    body: "Congratulations! You've earned a bonus of {$tier->bonus_amount} for completing {$tier->rides_count} rides.",
                    data: [
                        'msg_type' => 'bonus_earned',
                        'bonus_amount' => (string) $tier->bonus_amount,
                        'rides_count' => (string) $tier->rides_count,
                        'period_type' => $periodType,
                    ]
                );
            }
        }

        return $awarded;
    }

    /**
     * Admin manually grants a bonus to a specific driver.
     */
    public function grantManualBonus(User $driver, float $amount, User $admin, ?string $note = null): DriverBonusGrant
    {
        $ridesCount = $this->getDriverRidesCount($driver->id, 'all_time');

        $grant = $this->creditBonusToWallet(
            driver: $driver,
            amount: $amount,
            type: 'manual',
            tierId: null,
            grantedBy: $admin->id,
            note: $note ?? 'Manual bonus from admin',
            ridesCount: $ridesCount,
            periodLabel: null,
        );

        // Notify driver
        $this->notifyDriver(
            driver: $driver,
            title: 'You received a bonus!',
            body: "The admin has granted you a bonus of {$amount}. Check your wallet!",
            data: [
                'msg_type' => 'manual_bonus',
                'bonus_amount' => (string) $amount,
                'note' => $note ?? '',
            ]
        );

        return $grant;
    }

    /**
     * Credit bonus amount to driver's wallet and record the grant.
     */
    private function creditBonusToWallet(
        User $driver,
        float $amount,
        string $type,
        ?int $tierId,
        ?int $grantedBy,
        string $note,
        int $ridesCount,
        ?string $periodLabel
    ): DriverBonusGrant {
        DB::transaction(function () use ($driver, $amount, $type, $tierId, $grantedBy, $note, $ridesCount, $periodLabel, &$grant) {
            $driver->increment('wallet', $amount);

            Transaction::create([
                'user_id' => null,
                'driver_id' => $driver->id,
                'amount' => $amount,
                'description' => $note,
            ]);

            WalletRequest::create([
                'driver_id' => $driver->id,
                'amount' => $amount,
                'type' => 'deposit',
                'status' => 'approved',
                'note' => $note,
            ]);

            $grant = DriverBonusGrant::create([
                'driver_id' => $driver->id,
                'amount' => $amount,
                'type' => $type,
                'bonus_tier_id' => $tierId,
                'granted_by' => $grantedBy,
                'note' => $note,
                'rides_count' => $ridesCount,
                'period_label' => $periodLabel,
            ]);
        });

        Log::info("Bonus granted to driver {$driver->id}", [
            'amount' => $amount,
            'type' => $type,
            'note' => $note,
        ]);

        return $grant;
    }

    /**
     * Get leaderboard for a given period type.
     * Returns paginated list of drivers ranked by completed rides count.
     */
    public function getLeaderboard(string $periodType = 'monthly', int $limit = 50): array
    {
        [$startDate, $endDate] = $this->getPeriodDates($periodType);

        $query = DB::table('rides')
            ->join('users', 'rides.driver_id', '=', 'users.id')
            ->whereIn('rides.status', ['completed', 'finshed'])
            ->where('users.role', 'driver')
            ->select(
                'users.id as driver_id',
                'users.name',
                'users.image',
                DB::raw('COUNT(rides.id) as rides_count'),
                DB::raw('SUM(rides.calculated_final_price) as total_earned'),
            )
            ->groupBy('users.id', 'users.name', 'users.image')
            ->orderByDesc('rides_count')
            ->limit($limit);

        if ($startDate) {
            $query->whereBetween('rides.completed_at', [$startDate, $endDate]);
        }

        $rows = $query->get();

        return $rows->map(function ($row, $index) {
            return [
                'rank' => $index + 1,
                'driver_id' => $row->driver_id,
                'name' => $row->name,
                'image' => $row->image ? asset('storage/' . $row->image) : null,
                'rides_count' => (int) $row->rides_count,
                'total_earned' => round((float) $row->total_earned, 2),
            ];
        })->toArray();
    }

    /**
     * Get a specific driver's rank and stats for a period.
     */
    public function getDriverStats(User $driver, string $periodType = 'monthly'): array
    {
        [$startDate, $endDate] = $this->getPeriodDates($periodType);

        $ridesQuery = Ride::where('driver_id', $driver->id)
            ->whereIn('status', ['completed', 'finshed']);

        if ($startDate) {
            $ridesQuery->whereBetween('completed_at', [$startDate, $endDate]);
        }

        $ridesCount = $ridesQuery->count();
        $totalEarned = $ridesQuery->sum('calculated_final_price');

        // Calculate rank
        $higherOrEqualQuery = DB::table('rides')
            ->join('users', 'rides.driver_id', '=', 'users.id')
            ->whereIn('rides.status', ['completed', 'finshed'])
            ->where('users.role', 'driver')
            ->where('rides.driver_id', '!=', $driver->id)
            ->select('rides.driver_id', DB::raw('COUNT(rides.id) as cnt'))
            ->groupBy('rides.driver_id')
            ->having('cnt', '>', $ridesCount);

        if ($startDate) {
            $higherOrEqualQuery->whereBetween('rides.completed_at', [$startDate, $endDate]);
        }

        $rank = $higherOrEqualQuery->get()->count() + 1;

        // Next bonus tier
        $nextTier = BonusTier::where('is_active', true)
            ->where('period_type', $periodType)
            ->where('rides_count', '>', $ridesCount)
            ->orderBy('rides_count', 'asc')
            ->first();

        // Total bonuses earned this period
        $periodLabel = $this->getPeriodLabel($periodType);
        $bonusEarnedThisPeriod = DriverBonusGrant::where('driver_id', $driver->id)
            ->where('period_label', $periodLabel)
            ->sum('amount');

        $allTimeBonuses = DriverBonusGrant::where('driver_id', $driver->id)->sum('amount');

        return [
            'driver_id' => $driver->id,
            'name' => $driver->name,
            'image' => $driver->image ? asset('storage/' . $driver->image) : null,
            'rank' => $rank,
            'rides_count' => $ridesCount,
            'total_earned' => round((float) $totalEarned, 2),
            'period_type' => $periodType,
            'period_label' => $periodLabel,
            'bonus_earned_this_period' => round((float) $bonusEarnedThisPeriod, 2),
            'all_time_bonuses' => round((float) $allTimeBonuses, 2),
            'next_tier' => $nextTier ? [
                'rides_needed' => $nextTier->rides_count - $ridesCount,
                'rides_count' => $nextTier->rides_count,
                'bonus_amount' => $nextTier->bonus_amount,
                'tier_name' => $nextTier->name,
            ] : null,
        ];
    }

    /**
     * Count driver completed rides for a given period.
     */
    public function getDriverRidesCount(int $driverId, string $periodType = 'monthly'): int
    {
        [$startDate, $endDate] = $this->getPeriodDates($periodType);

        $query = Ride::where('driver_id', $driverId)
            ->whereIn('status', ['completed', 'finshed']);

        if ($startDate) {
            $query->whereBetween('completed_at', [$startDate, $endDate]);
        }

        return $query->count();
    }

    private function getPeriodDates(string $periodType): array
    {
        return match ($periodType) {
            'weekly' => [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek(),
            ],
            'monthly' => [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth(),
            ],
            default => [null, null],
        };
    }

    private function getPeriodLabel(string $periodType): ?string
    {
        return match ($periodType) {
            'weekly' => Carbon::now()->format('Y-W'),
            'monthly' => Carbon::now()->format('Y-m'),
            default => null,
        };
    }

    private function notifyDriver(User $driver, string $title, string $body, array $data = []): void
    {
        try {
            Notification::create([
                'driver_id' => $driver->id,
                'type' => 'driver',
                'title' => $title,
                'message' => $body,
            ]);

            if ($driver->fcm_token) {
                FcmHelper::sendPushNotification($driver->fcm_token, $title, $body, $data);
            }
        } catch (\Exception $e) {
            Log::error("Failed to notify driver {$driver->id} about bonus: " . $e->getMessage());
        }
    }
}
