<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Rating;
use App\Helpers\RideHelper;
use App\Helpers\ExcelExportHelper;
use App\Models\AppSetting;
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

        $users = $this->filteredUsersQuery($request)
            ->orderBy($sortField, $sortOrder)
            ->paginate(30);

        return view('users.index', compact('users', 'sortField', 'sortOrder'));
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

    protected function filteredUsersQuery(Request $request)
    {
        $keyword = $request->get('keyword');

        return User::where('role', 'user')
            ->with('zone')
            ->when($keyword, function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'LIKE', "%{$keyword}%")
                        ->orWhere('email', 'LIKE', "%{$keyword}%")
                        ->orWhere('phone', 'LIKE', "%{$keyword}%");
                });
            });
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(StoreUserRequest $request)
    {
        $request->setRole('user');

        User::create($request->validated());
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

        return view('users.show', compact('user', 'userRating', 'rideStatistics', 'recentRides', 'signupGiftAmount'));
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
        return view('users.edit', compact('user'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
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
