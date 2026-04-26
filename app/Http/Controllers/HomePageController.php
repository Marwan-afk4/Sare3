<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Ride;
use App\Services\FirebaseService;
use Illuminate\Http\Request;

class HomePageController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    public function index()
    {
        $userCount = User::where('role', 'user')->count();
        $driverCount = User::where('role', 'driver')->count();

        $userMonthlyCounts = $this->getMonthlyCounts(User::where('role', 'user'));
        $driverMonthlyCounts = $this->getMonthlyCounts(User::where('role', 'driver'));

        // Get active rides for tracking widget
        $activeRides = Ride::with(['user', 'driver', 'carCategory'])
            ->whereIn('status', ['accepted', 'waiting_user', 'in_progress'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get availability from Database
        $availableDriversCount = User::where('role', 'driver')->where('is_available', true)->count();
        $unavailableDriversCount = User::where('role', 'driver')->where('is_available', false)->count();

        // Get all drivers with status and location from DB
        $driversFromDb = User::where('role', 'driver')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get(['id', 'name', 'is_available', 'latitude', 'longitude', 'bearing']);
        $driverNames = $driversFromDb->pluck('name', 'id')->toArray();
        
        $availableDrivers = [];
        $unavailableDrivers = [];

        foreach ($driversFromDb as $driver) {
            $id = $driver->id;
            
            // Use database coordinates if they exist
            if ($driver->latitude && $driver->longitude) {
                $driverData = [
                    'driver_id' => $id,
                    'name' => $driver->name,
                    'latitude' => (float) $driver->latitude,
                    'longitude' => (float) $driver->longitude,
                    'bearing' => (float) ($driver->bearing ?? 0),
                    'is_available' => (bool) $driver->is_available
                ];

                if ($driver->is_available) {
                    $availableDrivers[$id] = $driverData;
                } else {
                    $unavailableDrivers[$id] = $driverData;
                }
            }
        }

        return view('home.welcome', compact(
            'userCount',
            'driverCount',
            'userMonthlyCounts',
            'driverMonthlyCounts',
            'activeRides',
            'availableDrivers',
            'availableDriversCount',
            'unavailableDrivers',
            'unavailableDriversCount',
            'driverNames'
        ));
    }

    private function getMonthlyCounts($query)
    {
        // Get counts grouped by month
        $counts = $query->selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->whereYear('created_at', now()->year)
            ->groupBy('month')
            ->pluck('count', 'month');

        // Initialize all 12 months with 0
        $monthlyCounts = array_fill(1, 12, 0);

        // Replace with actual values
        foreach ($counts as $month => $count) {
            $monthlyCounts[$month] = $count;
        }

        // Return indexed array (0-based index for JS)
        return array_values($monthlyCounts);
    }
}
