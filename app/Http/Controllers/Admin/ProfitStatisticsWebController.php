<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RideProfit;
use App\Models\Zone;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfitStatisticsWebController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', 'month');
        $zoneId = $request->get('zone');
        
        // Get all zones with their profit counts for the current period
        $zones = Zone::withCount(['rides as profit_rides_count' => function ($query) use ($period) {
            $query->whereHas('profit', function ($profitQuery) use ($period) {
                switch ($period) {
                    case 'day':
                        $profitQuery->whereDate('processed_at', Carbon::today());
                        break;
                    case 'week':
                        $profitQuery->whereBetween('processed_at', [
                            Carbon::now()->startOfWeek(),
                            Carbon::now()->endOfWeek()
                        ]);
                        break;
                    case 'month':
                        $profitQuery->whereMonth('processed_at', Carbon::now()->month)
                                   ->whereYear('processed_at', Carbon::now()->year);
                        break;
                    case 'year':
                        $profitQuery->whereYear('processed_at', Carbon::now()->year);
                        break;
                }
            });
        }])->get();
        
        // Count rides with no zone
        $profitsWithNoZoneCount = RideProfit::whereHas('ride', function ($query) {
            $query->whereNull('zone_id');
        });
        $this->applyPeriodFilterToProfit($profitsWithNoZoneCount, $period);
        $profitsWithNoZoneCount = $profitsWithNoZoneCount->count();
        
        // Get current period statistics
        $currentStats = $this->getPeriodStatistics($period, $zoneId);
        
        // Get daily breakdown for charts
        $dailyBreakdown = $this->getDailyBreakdown($period, $zoneId);
        
        // Get top drivers
        $topDrivers = $this->getTopDrivers($period, 5, $zoneId);
        
        return view('admin.profit-statistics.index', compact(
            'currentStats', 
            'dailyBreakdown', 
            'topDrivers', 
            'period',
            'zones',
            'profitsWithNoZoneCount',
            'zoneId'
        ));
    }

    public function history(Request $request)
    {
        $profits = RideProfit::with(['ride:id,pickup_address,dropoff_address', 'driver:id,name,phone'])
            ->orderByDesc('processed_at')
            ->paginate(20);

        return view('admin.profit-statistics.history', compact('profits'));
    }

    private function getPeriodStatistics($period, $zoneId = null)
    {
        $query = RideProfit::query();

        // Apply zone filter
        if ($zoneId !== null) {
            $query->whereHas('ride', function ($q) use ($zoneId) {
                if ($zoneId === 'no_zone') {
                    $q->whereNull('zone_id');
                } else {
                    $q->where('zone_id', $zoneId);
                }
            });
        }

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

    private function getDailyBreakdown($period, $zoneId = null)
    {
        $query = RideProfit::selectRaw('
            DATE(processed_at) as date,
            COUNT(*) as rides_count,
            SUM(admin_profit_amount) as daily_profit
        ');

        // Apply zone filter
        if ($zoneId !== null) {
            $query->whereHas('ride', function ($q) use ($zoneId) {
                if ($zoneId === 'no_zone') {
                    $q->whereNull('zone_id');
                } else {
                    $q->where('zone_id', $zoneId);
                }
            });
        }

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

    private function getTopDrivers($period, $limit = 10, $zoneId = null)
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

        // Apply zone filter
        if ($zoneId !== null) {
            $query->whereHas('ride', function ($q) use ($zoneId) {
                if ($zoneId === 'no_zone') {
                    $q->whereNull('zone_id');
                } else {
                    $q->where('zone_id', $zoneId);
                }
            });
        }

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

    private function applyPeriodFilterToProfit($query, $period)
    {
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
    }
}