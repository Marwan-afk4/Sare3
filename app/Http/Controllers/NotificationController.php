<?php

namespace App\Http\Controllers;

use App\Enums\OtpTypes;
use App\Helpers\FcmHelper;
use App\Models\Notification;
use App\Models\Driver;


use Illuminate\Http\Request;
use App\Http\Requests\StoreNotificationRequest;
use App\Http\Requests\UpdateNotificationRequest;
use App\Http\Controllers\Controller;
use App\Models\User;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $sortField = $request->get('sort', 'id');
        $sortOrder = $request->get('order', 'ASC');
        $notifications = Notification::with(['driver'])->orderBy($sortField, $sortOrder)->paginate(30);
        return view('notifications.index', compact('notifications', 'sortField', 'sortOrder'));
    }

    public function create()
    {
        $drivers = User::where('role', 'driver')
            ->orderBy('name')->pluck('name', 'id')->toArray();
        $types = OtpTypes::labels();
        return view('notifications.create', compact('drivers', 'types'));
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $validated = $request->validate([
            'type'      => 'required|in:user,driver', // المرسل إليه: user أو driver
            'title'     => 'required|string|max:255',
            'message'   => 'required|string|max:5000',
            'driver_id' => 'nullable|exists:users,id',
            'data'      => 'nullable|array', // بيانات إضافية للـ payload
        ]);

        // خزّن الإشعار في جدولك
        $notification = Notification::create([
            'type'      => $validated['type'],
            'title'     => $validated['title'],
            'message'   => $validated['message'],
            'driver_id' => $validated['driver_id'] ?? null,
            'user_id'   => $user->id, // المستخدم الذي أرسل الإشعار
        ]);

        // حدّد الجمهور
        if ($validated['type'] === 'driver') {
            $query = User::where('role', 'driver');
            if (!empty($validated['driver_id'])) {
                $query->where('id', $validated['driver_id']);
            }
        } else {
            $query = User::where('role', 'user');
        }

        $tokens = $query
            ->whereNotNull('fcm_token')
            ->where('fcm_token', '!=', '')
            ->pluck('fcm_token')
            ->unique()
            ->values()
            ->all();

        if (empty($tokens)) {
            return back()->with('error', __('No FCM tokens found for the selected audience.'));
        }

        $extraData = $validated['data'] ?? [];

        $responses = [];
        foreach ($tokens as $token) {
            $responses[] = [
                'token'    => $token,
                'response' => FcmHelper::sendPushNotification(
                    $token,
                    $validated['title'],
                    $validated['message'],
                    $extraData // هتبقى [] لو null
                ),
            ];
        }

        return redirect()
            ->route('notifications.index')
            ->with('success', __('Notification sent to :n recipients.', ['n' => count($tokens)]));
    }

    public function show(Notification $notification)
    {
        return view('notifications.show', compact('notification'));
    }
}
