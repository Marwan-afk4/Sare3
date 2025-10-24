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
        $sortField = $request->get('sort', 'id');
        $sortOrder = $request->get('order', 'ASC');
        $walletRequests = WalletRequest::with(['driver'])->orderBy($sortField, $sortOrder)->paginate(30);
        return view('wallet-requests.index', compact('walletRequests', 'sortField', 'sortOrder'));
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
        return view('wallet-requests.edit', compact('walletRequest', 'drivers','statuses'));
    }

    public function update(UpdateWalletRequestRequest $request, WalletRequest $walletRequest)
    {
        $walletRequest->update($request->validated());

        $walletRequestsMessages = WalletRequestsMessage::create([
            'admin_id' => auth()->id(),
            'driver_id' => $walletRequest->driver_id,
            'wallet_request_id' => $walletRequest->id,
            'admin_message' => $request->admin_message??null,
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
}
