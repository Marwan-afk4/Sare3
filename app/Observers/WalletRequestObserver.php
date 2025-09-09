<?php

namespace App\Observers;

use App\Enums\DriverStatus;
use App\Enums\WalletRequestType;
use App\Models\WalletRequest;

class WalletRequestObserver
{
    /**
     * Handle the WalletRequest "created" event.
     *
     * @param  \App\Models\WalletRequest  $walletRequest
     * @return void
     */
    public function created(WalletRequest $walletRequest)
    {
        if ($walletRequest->status === DriverStatus::Approved) {
            $this->applyWalletChange($walletRequest, 'add');
        }
    }

    /**
     * Handle the WalletRequest "updated" event.
     *
     * @param  \App\Models\WalletRequest  $walletRequest
     * @return void
     */
    public function updated(WalletRequest $walletRequest)
    {
        if ($walletRequest->isDirty('status')) {
            $oldStatus = $walletRequest->getOriginal('status');
            $newStatus = $walletRequest->status;

            if ($oldStatus !== DriverStatus::Approved && $newStatus === DriverStatus::Approved) {
                $this->applyWalletChange($walletRequest, 'add');
            } elseif ($oldStatus === DriverStatus::Approved && $newStatus !== DriverStatus::Approved) {
                $this->applyWalletChange($walletRequest, 'subtract');
            }
        }
    }

    /**
     * Apply wallet change depending on type and operation.
     *
     * @param \App\Models\WalletRequest $walletRequest
     * @param string $operation 'add' or 'subtract'
     * @return void
     */
    protected function applyWalletChange(WalletRequest $walletRequest, string $operation)
    {
        $driver = $walletRequest->driver;
        $amount = $walletRequest->amount;

        if ($operation === 'add') {
            if ($walletRequest->type === WalletRequestType::Deposit) {
                $driver->wallet += $amount;
            } elseif ($walletRequest->type === WalletRequestType::Withdraw) {
                $driver->wallet -= $amount;
            }
        } elseif ($operation === 'subtract') {
            if ($walletRequest->type === WalletRequestType::Deposit) {
                $driver->wallet -= $amount;
            } elseif ($walletRequest->type === WalletRequestType::Withdraw) {
                $driver->wallet += $amount;
            }
        }

        // ماينفعش الرصيد يبقى أقل من 0
        if ($driver->wallet < 0) {
            $driver->wallet = 0;
        }

        $driver->save();
    }

}
