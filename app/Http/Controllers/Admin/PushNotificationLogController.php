<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\FcmHelper;
use App\Http\Controllers\Controller;
use App\Models\PushNotificationLog;
use App\Models\User;
use Illuminate\Http\Request;

class PushNotificationLogController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $role = $request->query('role');
        $msgType = $request->query('msg_type');
        $rideId = $request->query('ride_id');
        $deliveryId = $request->query('delivery_id');
        $problems = $request->boolean('problems');
        $search = trim((string) $request->query('q', ''));

        $logs = PushNotificationLog::query()
            ->with(['recipient:id,name,phone,role'])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($role, fn ($query) => $query->where('recipient_role', $role))
            ->when($msgType, fn ($query) => $query->where('msg_type', $msgType))
            ->when($rideId, fn ($query) => $query->where('ride_id', $rideId))
            ->when($deliveryId, fn ($query) => $query->where('delivery_id', $deliveryId))
            ->when($problems, fn ($query) => $query->whereIn('status', ['failed', 'skipped', 'exception']))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('title', 'like', "%{$search}%")
                        ->orWhere('error_code', 'like', "%{$search}%")
                        ->orWhere('error_message', 'like', "%{$search}%");

                    if (ctype_digit($search)) {
                        $inner->orWhere('recipient_id', (int) $search)
                            ->orWhere('ride_id', (int) $search)
                            ->orWhere('delivery_id', (int) $search);
                    }

                    $inner->orWhereHas('recipient', function ($recipient) use ($search) {
                        $recipient->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
                });
            })
            ->latest('id')
            ->paginate(50)
            ->withQueryString();

        $since = now()->subDay();
        $counts = PushNotificationLog::query()
            ->where('created_at', '>=', $since)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $msgTypes = PushNotificationLog::query()
            ->whereNotNull('msg_type')
            ->distinct()
            ->orderBy('msg_type')
            ->pluck('msg_type');

        $drivers = User::query()
            ->where('role', 'driver')
            ->orderBy('name')
            ->get(['id', 'name', 'phone', 'fcm_token']);

        return view('admin.push-logs.index', [
            'currentPage' => 'push-logs',
            'logs' => $logs,
            'drivers' => $drivers,
            'counts' => $counts,
            'msgTypes' => $msgTypes,
            'filters' => [
                'status' => $status,
                'role' => $role,
                'msg_type' => $msgType,
                'ride_id' => $rideId,
                'delivery_id' => $deliveryId,
                'problems' => $problems,
                'q' => $search,
            ],
        ]);
    }

    public function sendTestRide(Request $request)
    {
        $data = $request->validate([
            'driver_id' => ['required', 'integer'],
        ]);

        $driver = User::query()
            ->where('role', 'driver')
            ->whereKey($data['driver_id'])
            ->first();

        if (!$driver) {
            return back()->with('error', __('Choose a driver.'));
        }

        $title = 'طلب رحلة جديد';
        $body = 'إشعار تجريبي من لوحة التحكم. لا توجد رحلة للقبول.';
        $result = FcmHelper::sendPushNotification(
            $driver->fcm_token,
            $title,
            $body,
            [
                'msg_type' => 'ride_request',
                'test' => '1',
            ],
            [
                'recipient_id' => $driver->id,
                'recipient_role' => 'driver',
            ]
        );

        if (is_array($result) && isset($result['name']) && !isset($result['error'])) {
            return back()->with('success', __('Firebase accepted the test ride alert for :name. Ask the driver if the phone showed it.', [
                'name' => $driver->name,
            ]));
        }

        if (($result['error'] ?? null) === 'No FCM token') {
            return back()->with('error', __(':name has no saved notification token, so the test was not sent.', [
                'name' => $driver->name,
            ]));
        }

        return back()->with('error', __('Firebase did not accept the test ride alert for :name. The row below has the reason.', [
            'name' => $driver->name,
        ]));
    }
}
