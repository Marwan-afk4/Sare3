<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Rating;
use App\Helpers\RideHelper;
use Illuminate\Http\Request;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $sortField = $request->get('sort', 'id');
        $sortOrder = $request->get('order', 'ASC');
        $keyword = $request->get('keyword');

        $users = User::where('role', 'user')
            ->with('zone')
            ->when($keyword, function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'LIKE', "%{$keyword}%")
                    ->orWhere('email', 'LIKE', "%{$keyword}%")
                    ->orWhere('phone', 'LIKE', "%{$keyword}%");
                });
            })
        ->orderBy($sortField, $sortOrder)->paginate(30);
        return view('users.index', compact('users', 'sortField', 'sortOrder'));
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

        return view('users.show', compact('user', 'userRating', 'rideStatistics', 'recentRides'));
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
