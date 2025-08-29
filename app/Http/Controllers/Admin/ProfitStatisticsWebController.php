<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RideProfit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfitStatisticsWebController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', 'month');
        
        // Get current period statistics
        $currentStats = $this->getPeriodStatistics($period);
        
        // Get daily breakdown for charts
        $dailyBreakdown = $this->getDailyBreakdown($period);
        
        // Get top drivers
        $topDrivers = $this->getTopDrivers($period, 5);
        
        return view('admin.profit-statistics.index', compact(
            'currentStats', 
            'dailyBreakdown', 
            'topDrivers', 
            'period'
        ));
    }

    public function history(Request $request)
    {
        $profits = RideProfit::with(['ride:id,pickup_address,dropoff_address', 'driver:id,name,phone'])
            ->orderByDesc('processed_at')
            ->paginate(20);

        return view('admin.profit-statistics.history', compact('profits'));
    }

    private function getPeriodStatistics($period)
    {
        $query = RideProfit::query();

        switch ($period) {
            case 'day':
                $query->whereDate('processed_at', Carbon::today());
                break;
            case 'week':
                $query->whereBetween('processed_at', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ]);
                break;
            case 'month':
                $query->whereMonth('processed_at', Carbon::now()->month)
                      ->whereYear('processed_at', Carbon::now()->year);
                break;
            case 'year':
                $query->whereYear('processed_at', Carbon::now()->year);
                break;
        }

        return $query->selectRaw('
            COUNT(*) as total_rides,
            SUM(total_fare) as total_fare,
            SUM(admin_profit_amount) as total_admin_profit,
            SUM(driver_amount) as total_driver_amount,
            AVG(admin_profit_percentage) as avg_profit_percentage
        ')->first();
    }

    private function getDailyBreakdown($period)
    {
        $query = RideProfit::selectRaw('
            DATE(processed_at) as date,
            COUNT(*) as rides_count,
            SUM(admin_profit_amount) as daily_profit
        ');

        switch ($period) {
            case 'week':
                $query->whereBetween('processed_at', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ]);
                break;
            case 'month':
                $query->whereMonth('processed_at', Carbon::now()->month)
                      ->whereYear('processed_at', Carbon::now()->year);
                break;
            case 'year':
                $query->whereYear('processed_at', Carbon::now()->year);
                break;
            default:
                $query->whereBetween('processed_at', [
                    Carbon::now()->subDays(7),
                    Carbon::now()
                ]);
        }

        return $query->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function getTopDrivers($period, $limit = 10)
    {
        $query = RideProfit::with('driver:id,name,phone')
            ->selectRaw('
                driver_id,
                COUNT(*) as total_rides,
                SUM(total_fare) as total_fare,
                SUM(admin_profit_amount) as total_admin_profit,
                SUM(driver_amount) as total_driver_earnings
            ')
            ->groupBy('driver_id');

        switch ($period) {
            case 'week':
                $query->whereBetween('processed_at', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ]);
                break;
            case 'month':
                $query->whereMonth('processed_at', Carbon::now()->month)
                      ->whereYear('processed_at', Carbon::now()->year);
                break;
            case 'year':
                $query->whereYear('processed_at', Carbon::now()->year);
                break;
        }

        return $query->orderByDesc('total_driver_earnings')
            ->limit($limit)
            ->get();
    }
}