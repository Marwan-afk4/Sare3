<?php

namespace App\Services;

use App\Helpers\FcmHelper;
use App\Models\AppSetting;
use App\Models\Notification;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SignupGiftService
{
    public const TRANSACTION_DESCRIPTION = 'Signup welcome gift';

    public const REVERSAL_DESCRIPTION = 'Signup welcome gift reversed (became driver)';

    public function isEnabled(): bool
    {
        return (bool) AppSetting::isSignupGiftEnabled();
    }

    public function getAmount(): float
    {
        return AppSetting::getSignupGiftAmount();
    }

    public function hasReceived(User $user): bool
    {
        return $user->signup_gift_received_at !== null;
    }

    /**
     * A passenger who has never received the welcome gift.
     */
    public function isPending(User $user): bool
    {
        return $user->isUser() && ! $this->hasReceived($user);
    }

    /**
     * Gift is active when enabled and amount is greater than zero.
     */
    public function isConfigured(): bool
    {
        return $this->isEnabled() && $this->getAmount() > 0;
    }

    /**
     * Auto-grant only on a brand-new completed signup, when the feature is on.
     */
    public function grantIfEligible(User $user): ?array
    {
        if (! $this->isConfigured()) {
            Log::debug('Signup gift skipped: not configured', [
                'user_id' => $user->id,
                'enabled' => $this->isEnabled(),
                'amount' => $this->getAmount(),
            ]);

            return null;
        }

        if (! $user->isUser()) {
            return null;
        }

        return $this->grant($user, $this->getAmount(), null);
    }

    /**
     * Call after a passenger completes their profile (name set for the first time).
     */
    public function grantOnRegistrationComplete(User $user, bool $wasProfileIncomplete): ?array
    {
        if (! $wasProfileIncomplete || blank($user->name)) {
            return null;
        }

        return $this->grantIfEligible($user);
    }

    /**
     * Take back leftover passenger welcome-gift credit when the same phone becomes a driver.
     * Other wallet funds are left untouched. Safe to call more than once.
     *
     * @return array{amount: float, wallet: float}|null
     */
    public function revokeWhenBecomingDriver(User $user): ?array
    {
        $revoked = null;

        DB::transaction(function () use ($user, &$revoked) {
            $locked = User::where('id', $user->id)->lockForUpdate()->first();

            if (! $locked || $locked->signup_gift_received_at === null) {
                return;
            }

            $alreadyReversed = Transaction::where('user_id', $locked->id)
                ->where('description', self::REVERSAL_DESCRIPTION)
                ->exists();

            if ($alreadyReversed) {
                return;
            }

            $giftAmount = round((float) ($locked->signup_gift_amount ?? 0), 3);
            $currentWallet = (float) ($locked->wallet ?? 0);
            $deduct = $giftAmount > 0
                ? round(min(max($currentWallet, 0), $giftAmount), 3)
                : 0.0;

            if ($deduct > 0) {
                $locked->forceFill([
                    'wallet' => round($currentWallet - $deduct, 3),
                ])->save();
            }

            Transaction::create([
                'user_id' => $locked->id,
                'driver_id' => $locked->id,
                'amount' => -$deduct,
                'description' => self::REVERSAL_DESCRIPTION,
            ]);

            $user->setRawAttributes($locked->getAttributes());
            $user->syncOriginal();

            $revoked = [
                'amount' => $deduct,
                'wallet' => (float) ($locked->wallet ?? 0),
            ];
        });

        if ($revoked !== null) {
            Log::info("Signup gift reversed for user {$user->id} becoming a driver", [
                'amount' => $revoked['amount'],
                'wallet' => $revoked['wallet'],
            ]);
        }

        return $revoked;
    }

    /**
     * Admin grant. Uses the configured amount unless a custom amount is passed.
     */
    public function grantManually(User $user, User $admin, ?float $amount = null): array
    {
        $amount = $amount !== null ? round($amount, 2) : $this->getAmount();

        if ($amount <= 0) {
            throw new \InvalidArgumentException(__('Set a gift amount greater than zero before granting.'));
        }

        if (! $user->isUser()) {
            throw new \InvalidArgumentException(__('Signup gifts can only be given to passengers.'));
        }

        $result = $this->grant($user, $amount, $admin);

        if ($result === null) {
            throw new \RuntimeException(__('This user has already received a signup gift.'));
        }

        return $result;
    }

    /**
     * @param  iterable<int|User>  $users
     * @return array{granted: int, skipped: int, failed: int}
     */
    public function grantBulk(iterable $users, User $admin, ?float $amount = null): array
    {
        $amount = $amount !== null ? round($amount, 2) : $this->getAmount();

        if ($amount <= 0) {
            throw new \InvalidArgumentException(__('Set a gift amount greater than zero before granting.'));
        }

        $stats = ['granted' => 0, 'skipped' => 0, 'failed' => 0];

        foreach ($users as $user) {
            if (! $user instanceof User) {
                $user = User::find($user);
            }

            if (! $user || ! $user->isUser()) {
                $stats['skipped']++;
                continue;
            }

            try {
                $result = $this->grant($user, $amount, $admin);
                if ($result === null) {
                    $stats['skipped']++;
                } else {
                    $stats['granted']++;
                }
            } catch (\Throwable $e) {
                $stats['failed']++;
                Log::error("Failed to grant signup gift to user {$user->id}: ".$e->getMessage());
            }
        }

        return $stats;
    }

    public function pendingQuery(bool $completedProfilesOnly = true)
    {
        return User::query()
            ->where('role', 'user')
            ->whereNull('signup_gift_received_at')
            ->when($completedProfilesOnly, function ($query) {
                $query->where(function ($q) {
                    $q->where(function ($nameQuery) {
                        $nameQuery->whereNotNull('name')->where('name', '!=', '');
                    })->orWhereNotNull('email');
                });
            });
    }

    public function grantedQuery()
    {
        return User::query()
            ->where('role', 'user')
            ->whereNotNull('signup_gift_received_at');
    }

    /**
     * @return array{amount: float, wallet: float}|null  Null when the user already received the gift.
     */
    private function grant(User $user, float $amount, ?User $admin): ?array
    {
        $granted = null;

        DB::transaction(function () use ($user, $amount, $admin, &$granted) {
            $locked = User::where('id', $user->id)->lockForUpdate()->first();

            if (! $locked || $locked->signup_gift_received_at !== null) {
                return;
            }

            $currentWallet = (float) ($locked->wallet ?? 0);
            $locked->forceFill([
                'wallet' => round($currentWallet + $amount, 3),
                'signup_gift_received_at' => now(),
                'signup_gift_amount' => $amount,
                'signup_gift_granted_by' => $admin?->id,
            ])->save();

            Transaction::create([
                'user_id' => $locked->id,
                'driver_id' => null,
                'amount' => $amount,
                'description' => self::TRANSACTION_DESCRIPTION,
            ]);

            $user->setRawAttributes($locked->getAttributes());
            $user->syncOriginal();

            $granted = [
                'amount' => $amount,
                'wallet' => (float) $locked->wallet,
            ];
        });

        if ($granted === null) {
            return null;
        }

        $this->notifyUser($user, $granted['amount']);

        Log::info("Signup gift granted to user {$user->id}", [
            'amount' => $granted['amount'],
            'granted_by' => $admin?->id,
        ]);

        return $granted;
    }

    private function notifyUser(User $user, float $amount): void
    {
        $formatted = number_format($amount, 2);
        $title = 'هدية ترحيبية';
        $body = "تم إضافة {$formatted} د.أ إلى محفظتك كهدية انضمام. استمتع برحلاتك مع سريع!";

        try {
            Notification::create([
                'user_id' => $user->id,
                'type' => 'user',
                'title' => $title,
                'message' => $body,
            ]);

            if ($user->fcm_token) {
                FcmHelper::sendPushNotification($user->fcm_token, $title, $body, [
                    'msg_type' => 'signup_gift',
                    'amount' => (string) $amount,
                    'wallet' => (string) $user->wallet,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error("Failed to notify user {$user->id} about signup gift: ".$e->getMessage());
        }
    }
}
