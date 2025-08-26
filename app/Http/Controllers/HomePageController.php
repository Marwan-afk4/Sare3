<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Ride;
use Illuminate\Http\Request;

class HomePageController extends Controller
{
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

        return view('home.welcome', compact(
            'userCount',
            'driverCount',
            'userMonthlyCounts',
            'driverMonthlyCounts',
            'activeRides'
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
