<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Zone;
use App\Models\Rating;
use App\Helpers\RideHelper;
use App\Helpers\ExcelExportHelper;
use App\Models\AppSetting;
use App\Models\OtpLimit;
use Illuminate\Http\Request;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $sortField = $request->get('sort', 'id');
        $sortOrder = $request->get('order', 'DESC');

        $zones = Zone::withCount(['users as user_count' => function ($query) {
            $query->where('role', 'user');
        }])->orderBy('name')->get();

        $usersWithNoZoneCount = User::where('role', 'user')
            ->whereNull('zone_id')
            ->count();

        $newUsersTodayCount = User::where('role', 'user')
            ->whereDate('created_at', today())
            ->count();

        $users = $this->filteredUsersQuery($request)
            ->orderBy($sortField, $sortOrder)
            ->paginate(30)
            ->withQueryString();

        return view('users.index', compact(
            'users',
            'sortField',
            'sortOrder',
            'zones',
            'usersWithNoZoneCount',
            'newUsersTodayCount'
        ));
    }

    public function export(Request $request): StreamedResponse
    {
        $sortField = $request->get('sort', 'id');
        $sortOrder = $request->get('order', 'DESC');

        $users = $this->filteredUsersQuery($request)
            ->orderBy($sortField, $sortOrder)
            ->get();

        $headers = [
            __('Id'),
            __('Name'),
            __('Email'),
            __('Phone'),
            __('Wallet'),
            __('Activity'),
            __('Zone'),
            __('Gender'),
            __('Created At'),
        ];

        $rows = $users->map(function (User $user) {
            return [
                $user->id,
                $user->name,
                $user->email,
                $user->phone,
                $user->wallet,
                $user->activity?->label(),
                $user->zone?->name,
                $user->gender,
                optional($user->created_at)->format('Y-m-d H:i:s'),
            ];
        });

        return ExcelExportHelper::download('users_' . now()->format('Y-m-d_His'), $headers, $rows);
    }

    public function exportPhones(Request $request): StreamedResponse
    {
        $phones = $this->filteredUsersQuery($request)
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->orderBy('id')
            ->pluck('phone');

        $rows = $phones->map(fn (string $phone) => [$phone]);

        return ExcelExportHelper::download('users_phones_' . now()->format('Y-m-d_His'), [__('Phone')], $rows);
    }

    protected function filteredUsersQuery(Request $request)
    {
        $keyword = $request->get('keyword');
        $zoneId = $request->get('zone');

        return User::where('role', 'user')
            ->with('zone')
            ->when($keyword, function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'LIKE', "%{$keyword}%")
                        ->orWhere('email', 'LIKE', "%{$keyword}%")
                        ->orWhere('phone', 'LIKE', "%{$keyword}%");
                });
            })
            ->when($zoneId !== null && $zoneId !== '', function ($query) use ($zoneId) {
                if ($zoneId === 'no_zone') {
                    $query->whereNull('zone_id');
                } else {
                    $query->where('zone_id', $zoneId);
                }
            })
            ->when($request->boolean('new_today'), function ($query) {
                $query->whereDate('created_at', today());
            });
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(StoreUserRequest $request)
    {
        $request->setRole('user');

        $user = User::create($request->validated());
        $user->refresh();

        if (empty($user->getAttributes()['password'])) {
            $user->password = $request->input('password');
            $user->save();
            $user->refresh();
        }

        if (empty($user->getAttributes()['password'])) {
            $user->delete();

            return back()
                ->withErrors(['password' => __('Failed to save password. Please try again.')])
                ->withInput();
        }

        return redirect()->route('users.index')->with('success',  __('Created successfully'));
    }

    public function show(User $user)
    {
        // Load user rides with relationships
        $user->load([
            'userRides' => function ($query) {
                $query->with(['driver.driverCars.carModel', 'carCategory'])
                      ->orderBy('created_at', 'desc');
            }
        ]);

        // Get user rating
        $userRating = Rating::where('ratee_id', $user->id)
            ->where('ratee_type', 'user')
            ->avg('rate');

        // Get ride statistics
        $rideStatistics = RideHelper::getUserRideStatistics($user->userRides);

        // Get recent rides (last 10)
        $recentRides = RideHelper::formatUserRideHistory($user->userRides->take(10));
        $signupGiftAmount = AppSetting::getSignupGiftAmount();
        $defaultOtpLimit = (int) (OtpLimit::where('type', 'user')->value('otp_limit') ?? 5);

        return view('users.show', compact('user', 'userRating', 'rideStatistics', 'recentRides', 'signupGiftAmount', 'defaultOtpLimit'));
    }

    public function updateOtpUsage(Request $request, User $user)
    {
        if ($user->role !== 'user') {
            abort(404);
        }

        if ($request->input('otp_action') === 'reset') {
            $user->otp_used = 0;
            $user->save();

            return redirect()
                ->route('users.show', $user)
                ->with('otp_success', __('OTP usage reset successfully.'));
        }

        $data = $request->validate([
            'otp_used' => 'required|integer|min:0|max:100000',
            'otp_limit' => 'required|integer|min:0|max:1000',
        ]);

        $user->otp_used = $data['otp_used'];
        $user->otp_limit = $data['otp_limit'];
        $user->save();

        return redirect()
            ->route('users.show', $user)
            ->with('otp_success', __('OTP usage updated successfully.'));
    }

    public function rideHistory(User $user, Request $request)
    {
        $status = $request->get('status', 'all');
        $perPage = $request->get('per_page', 20);

        // Build query
        $query = $user->userRides()
            ->with(['driver.driverCars.carModel', 'carCategory'])
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($status !== 'all') {
            if ($status === 'completed') {
                $query->whereIn('status', ['completed', 'finshed']);
            } else {
                $query->where('status', $status);
            }
        }

        // Paginate
        $rides = $query->paginate($perPage);

        // Format data
        $ridesData = RideHelper::formatUserRideHistory($rides->items());
        $rideStatistics = RideHelper::getUserRideStatistics(collect($rides->items()));

        return view('users.ride-history', compact('user', 'rides', 'ridesData', 'rideStatistics', 'status'));
    }

    public function edit(User $user)
    {
        $hasPassword = ! empty($user->getAttributes()['password']);

        return view('users.edit', compact('user', 'hasPassword'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        if ($request->filled('_password_action')) {
            if ($request->input('_password_action') === 'remove') {
                $user->forceFill(['password' => null])->save();

                return redirect()
                    ->route('users.edit', $user)
                    ->with('success', __('Password removed successfully.'));
            }

            $user->password = $request->input('user_password');
            $user->save();
            $user->refresh();

            if (empty($user->getAttributes()['password'])) {
                return back()
                    ->withErrors(['user_password' => __('Failed to save password. Please try again.')])
                    ->withInput(['_password_action' => 'set']);
            }

            return redirect()
                ->route('users.edit', $user)
                ->with('success', __('Password updated successfully.'));
        }

        $user->update($request->validated());
        return redirect()->route('users.index')->with('success',  __('Updated successfully.'));
    }

    public function destroy(User $user)
    {
        try {
            $user->delete();

            return redirect()
                ->route('users.index')
                ->with('success', __('User deleted successfully.'));
        } catch (\Exception $e) {
            return redirect()
                ->route('users.index')
                ->with('error', __('Failed to delete user. Please try again.'));
        }
    }

}
