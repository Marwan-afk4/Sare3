<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\RideProfit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfitStatisticsController extends Controller
{
    /**
     * Get profit statistics for a specific period
     */
    public function getProfitStatistics(Request $request)
    {
        $period = $request->get('period', 'month'); // day, week, month, year
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $query = RideProfit::query();

        // Apply date filters
        if ($startDate && $endDate) {
            $query->whereBetween('processed_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ]);
        } else {
            // Default period filters
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

        $statistics = $query->selectRaw('
            COUNT(*) as total_rides,
            SUM(total_fare) as total_fare,
            SUM(admin_profit_amount) as total_admin_profit,
            SUM(driver_amount) as total_driver_amount,
            AVG(admin_profit_percentage) as avg_profit_percentage
        ')->first();

        return response()->json([
            'message' => 'Profit statistics retrieved successfully.',
            'period' => $period,
            'statistics' => [
                'total_rides' => (int) $statistics->total_rides,
                'total_fare' => (float) $statistics->total_fare ?: 0,
                'total_admin_profit' => (float) $statistics->total_admin_profit ?: 0,
                'total_driver_amount' => (float) $statistics->total_driver_amount ?: 0,
                'average_profit_percentage' => round((float) $statistics->avg_profit_percentage ?: 0, 2)
            ]
        ]);
    }

    /**
     * Get daily profit breakdown for a month
     */
    public function getDailyProfitBreakdown(Request $request)
    {
        $month = $request->get('month', Carbon::now()->month);
        $year = $request->get('year', Carbon::now()->year);

        $dailyProfits = RideProfit::selectRaw('
            DATE(processed_at) as date,
            COUNT(*) as rides_count,
            SUM(admin_profit_amount) as daily_profit
        ')
        ->whereMonth('processed_at', $month)
        ->whereYear('processed_at', $year)
        ->groupBy('date')
        ->orderBy('date')
        ->get();

        return response()->json([
            'message' => 'Daily profit breakdown retrieved successfully.',
            'month' => $month,
            'year' => $year,
            'daily_profits' => $dailyProfits
        ]);
    }

    /**
     * Get top earning drivers
     */
    public function getTopEarningDrivers(Request $request)
    {
        $limit = $request->get('limit', 10);
        $period = $request->get('period', 'month');

        $query = RideProfit::with('driver:id,name,phone')
            ->selectRaw('
                driver_id,
                COUNT(*) as total_rides,
                SUM(total_fare) as total_fare,
                SUM(admin_profit_amount) as total_admin_profit,
                SUM(driver_amount) as total_driver_earnings
            ')
            ->groupBy('driver_id');

        // Apply period filter
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

        $topDrivers = $query->orderByDesc('total_driver_earnings')
            ->limit($limit)
            ->get();

        return response()->json([
            'message' => 'Top earning drivers retrieved successfully.',
            'period' => $period,
            'drivers' => $topDrivers
        ]);
    }

    /**
     * Get profit history with pagination
     */
    public function getProfitHistory(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        
        $profits = RideProfit::with(['ride:id,pickup_address,dropoff_address', 'driver:id,name,phone'])
            ->orderByDesc('processed_at')
            ->paginate($perPage);

        return response()->json([
            'message' => 'Profit history retrieved successfully.',
            'profits' => $profits
        ]);
    }
}