<?php

namespace App\Http\Controllers;

use App\Enums\DriverStatus;
use App\Models\WalletRequest;
use App\Models\Driver;


use Illuminate\Http\Request;
use App\Http\Requests\StoreWalletRequestRequest;
use App\Http\Requests\UpdateWalletRequestRequest;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WalletRequestsMessage;

class WalletRequestController extends Controller
{
    public function index(Request $request)
    {
        $sortField = $request->get('sort', 'name');
        $sortOrder = $request->get('order', 'ASC');
        $keyword = $request->get('keyword');
        $balanceOperator = $request->get('balance_operator');
        $balanceAmount = $request->get('balance_amount');
        $accountType = $request->get('account_type', 'drivers');
        if (!in_array($accountType, ['drivers', 'riders'], true)) {
            $accountType = 'drivers';
        }

        $role = $accountType === 'riders' ? 'delivery' : 'driver';

        $drivers = User::where('role', $role)
            ->when($keyword, function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'LIKE', "%{$keyword}%")
                        ->orWhere('phone', 'LIKE', "%{$keyword}%")
                        ->orWhere('email', 'LIKE', "%{$keyword}%");
                });
            })
            ->filterByWalletBalance($balanceOperator, $balanceAmount)
            ->orderBy($sortField, $sortOrder)
            ->paginate(30)
            ->withQueryString();

        return view('wallet-requests.index', compact(
            'drivers',
            'sortField',
            'sortOrder',
            'balanceOperator',
            'balanceAmount',
            'accountType'
        ));
    }

    public function create()
    {
        $drivers = User::orderBy('name')->pluck('name', 'id')->toArray();
        return view('wallet-requests.create', compact('drivers'));
    }

    public function store(StoreWalletRequestRequest $request)
    {
        WalletRequest::create($request->validated());
        return redirect()->route('wallet-requests.index')->with('success',  __('Created successfully'));
    }

    public function show(WalletRequest $walletRequest)
    {
        $walletRequest->load(['messages.admin', 'messages.driver', 'driver']);
        return view('wallet-requests.show', compact('walletRequest'));
    }

    public function edit(WalletRequest $walletRequest)
    {
        $drivers = User::orderBy('name')->pluck('name', 'id')->toArray();
        $statuses = DriverStatus::labels();
        return view('wallet-requests.edit', compact('walletRequest', 'drivers', 'statuses'));
    }

    public function update(UpdateWalletRequestRequest $request, WalletRequest $walletRequest)
    {
        $walletRequest->update($request->validated());

        $walletRequestsMessages = WalletRequestsMessage::create([
            'admin_id' => auth()->id(),
            'driver_id' => $walletRequest->driver_id,
            'wallet_request_id' => $walletRequest->id,
            'admin_message' => $request->admin_message ?? null,
            // 'driver_message' => $request->driver_message??null,
        ]);
        return redirect()->route('wallet-requests.index')->with('success', __('Updated successfully.'));
    }

    public function sendAcceptanceNotification(Request $request, WalletRequest $walletRequest)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
        ]);

        try {
            // Create notification record
            $notification = \App\Models\Notification::create([
                'type' => 'driver',
                'title' => $validated['title'],
                'message' => $validated['message'],
                'driver_id' => $walletRequest->driver_id,
                'user_id' => auth()->id(),
            ]);

            // Send Firebase notification to the specific driver
            if ($walletRequest->driver && $walletRequest->driver->fcm_token) {
                $extraData = [
                    'wallet_request_id' => $walletRequest->id,
                    'action' => 'wallet_approved',
                    'amount' => $walletRequest->amount,
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
                ];

                $response = \App\Helpers\FcmHelper::sendPushNotification(
                    $walletRequest->driver->fcm_token,
                    $validated['title'],
                    $validated['message'],
                    $extraData
                );

                return response()->json([
                    'success' => true,
                    'message' => __('Notification sent successfully'),
                    'fcm_response' => $response
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => __('Driver FCM token not found')
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Failed to send notification: ') . $e->getMessage()
            ], 500);
        }
    }

    public function addMessage(Request $request, WalletRequest $walletRequest)
    {
        $validated = $request->validate([
            'admin_message' => 'required|string|max:1000',
        ]);

        try {
            // Create the message
            WalletRequestsMessage::create([
                'wallet_request_id' => $walletRequest->id,
                'admin_id' => auth()->id(),
                'driver_id' => $walletRequest->driver_id,
                'admin_message' => $validated['admin_message'],
            ]);

            // Optionally send a Firebase notification to the driver about the new message
            if ($walletRequest->driver && $walletRequest->driver->fcm_token) {
                $extraData = [
                    'wallet_request_id' => $walletRequest->id,
                    'action' => 'new_message',
                    'message_type' => 'admin_message',
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
                ];

                \App\Helpers\FcmHelper::sendPushNotification(
                    $walletRequest->driver->fcm_token,
                    __('New Message from Admin'),
                    __('You have a new message regarding your wallet request #:id', ['id' => $walletRequest->id]),
                    $extraData
                );
            }

            return redirect()->route('wallet-requests.show', $walletRequest)
                ->with('success', __('Message sent successfully'));
        } catch (\Exception $e) {
            return redirect()->route('wallet-requests.show', $walletRequest)
                ->with('error', __('Failed to send message: ') . $e->getMessage());
        }
    }

    public function addToWallet(Request $request, User $driver)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'note' => 'nullable|string|max:500'
        ]);

        try {
            $admin = auth()->user();

            // Check if admin has wallet management permission
            if ($admin->can('إدارة طلبات المحفظة')) {
                // Check if admin has sufficient limit
                if ($admin->wallet_limit < $validated['amount']) {
                    return $this->walletRedirect($driver)
                        ->with('error', __('Insufficient wallet limit. Your current limit: ') . '$' . number_format($admin->wallet_limit, 2));
                }

                // Deduct from admin's limit
                $admin->decrement('wallet_limit', $validated['amount']);
            }

            // Add to driver wallet
            $driver->increment('wallet', $validated['amount']);

            // Create wallet request record for tracking
            WalletRequest::create([
                'driver_id' => $driver->id,
                'amount' => $validated['amount'],
                'type' => 'deposit',
                'status' => 'approved',
                'note' => $validated['note'] ?? 'Admin added to wallet',
            ]);

            return $this->walletRedirect($driver)
                ->with('success', __('Amount added successfully to wallet'));
        } catch (\Exception $e) {
            return $this->walletRedirect($driver)
                ->with('error', __('Failed to add amount: ') . $e->getMessage());
        }
    }

    public function subtractFromWallet(Request $request, User $driver)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'note' => 'nullable|string|max:500'
        ]);

        try {
            // Check if driver has sufficient balance
            if ($driver->wallet < $validated['amount']) {
                return $this->walletRedirect($driver)
                    ->with('error', __('Insufficient wallet balance. Current balance: ') . $driver->wallet);
            }

            // Subtract from wallet
            $driver->decrement('wallet', $validated['amount']);

            // Create wallet request record for tracking
            WalletRequest::create([
                'driver_id' => $driver->id,
                'amount' => $validated['amount'],
                'type' => 'deduction',
                'status' => 'approved',
                'note' => $validated['note'] ?? 'Admin deducted from wallet',
                'driver_wallet' => $driver->fresh()->wallet
            ]);

            return $this->walletRedirect($driver)
                ->with('success', __('Amount deducted successfully from wallet'));
        } catch (\Exception $e) {
            return $this->walletRedirect($driver)
                ->with('error', __('Failed to deduct amount: ') . $e->getMessage());
        }
    }

    public function walletHistory(User $driver)
    {
        $walletHistory = WalletRequest::where('driver_id', $driver->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('wallet-requests.history', compact('driver', 'walletHistory'));
    }

    protected function walletRedirect(User $account)
    {
        $accountType = $account->isDelivery() ? 'riders' : 'drivers';

        return redirect()->route('wallet-requests.index', ['account_type' => $accountType]);
    }

    public function adminLimits()
    {
        // Get all admins who have the wallet management permission
        $admins = User::where('role', 'admin')
            ->whereHas('roles.permissions', function ($query) {
                $query->where('name', 'إدارة طلبات المحفظة');
            })
            ->orWhereHas('permissions', function ($query) {
                $query->where('name', 'إدارة طلبات المحفظة');
            })
            ->orderBy('name')
            ->get();

        return view('wallet-requests.admin-limits', compact('admins'));
    }

    public function updateAdminLimit(Request $request, User $admin)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'action' => 'required|in:set,add,subtract'
        ]);

        try {
            if ($validated['action'] === 'set') {
                // Set the limit to specific amount
                $admin->update(['wallet_limit' => $validated['amount']]);
                $message = __('Admin wallet limit set successfully to: ') . '$' . number_format($validated['amount'], 2);
            } elseif ($validated['action'] === 'subtract') {
                if ($validated['amount'] < 0.01) {
                    return redirect()
                        ->back()
                        ->with('error', __('Amount must be at least 0.01.'));
                }
                if ($admin->wallet_limit < $validated['amount']) {
                    return redirect()
                        ->back()
                        ->with('error', __('Cannot subtract more than the current wallet limit.'));
                }
                $admin->decrement('wallet_limit', $validated['amount']);
                $message = __('Subtracted from admin wallet limit successfully. New limit: ') . '$' . number_format($admin->fresh()->wallet_limit, 2);
            } else {
                // Add to existing limit
                $admin->increment('wallet_limit', $validated['amount']);
                $message = __('Added to admin wallet limit successfully. New limit: ') . '$' . number_format($admin->fresh()->wallet_limit, 2);
            }

            return redirect()
                ->route('admins.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            return redirect()
                ->route('admins.index')
                ->with('error', __('Failed to update limit: ') . $e->getMessage());
        }
    }
}
