<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PushNotificationLog;
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

        return view('admin.push-logs.index', [
            'currentPage' => 'push-logs',
            'logs' => $logs,
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
}
