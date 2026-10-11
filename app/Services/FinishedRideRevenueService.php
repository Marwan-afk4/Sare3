<?php

namespace App\Services;

use App\Enums\RideStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FinishedRideRevenueService
{
    /**
     * Monthly fare totals for rides whose status is finished (منتهية).
     * A ride is placed in the month it actually ended.
     *
     * @return array{
     *     year: int,
     *     is_current_year: bool,
     *     months: list<array{month: int, label: string, total: float, rides: int, is_current: bool, is_best: bool}>,
     *     year_total: float,
     *     year_rides: int,
     *     average_fare: float,
     *     max_total: float,
     *     current_month_index: int|null,
     *     best_month_index: int|null,
     *     this_month: array{month: int, label: string, total: float, rides: int}|null,
     *     change_percent: float|null,
     *     available_years: list<int>
     * }
     */
    public function forYear(int $year): array
    {
        $currentYear = (int) now()->year;
        $year = max(2000, min($year, $currentYear));

        $monthSql = $this->monthSql();
        $yearSql = $this->yearSql();

        $rows = DB::table('rides')
            ->where('status', RideStatus::Finshed->value)
            ->whereRaw("{$yearSql} = ?", [$year])
            ->selectRaw("{$monthSql} as month, COUNT(*) as rides_count, COALESCE(SUM(COALESCE(calculated_final_price, 0)), 0) as total")
            ->groupByRaw($monthSql)
            ->get()
            ->keyBy(fn ($row) => (int) $row->month);

        $months = [];
        $bestIndex = null;
        $bestTotal = 0.0;

        for ($month = 1; $month <= 12; $month++) {
            $row = $rows->get($month);
            $total = round((float) ($row->total ?? 0), 2);
            $months[] = [
                'month' => $month,
                'label' => Carbon::create(null, $month, 1)->locale('en')->monthName,
                'total' => $total,
                'rides' => (int) ($row->rides_count ?? 0),
                'is_current' => false,
                'is_best' => false,
            ];

            if ($total > 0 && $total >= $bestTotal) {
                $bestTotal = $total;
                $bestIndex = $month - 1;
            }
        }

        if ($bestIndex !== null && $bestTotal > 0) {
            $months[$bestIndex]['is_best'] = true;
        }

        $yearTotal = round(array_sum(array_column($months, 'total')), 2);
        $yearRides = (int) array_sum(array_column($months, 'rides'));
        $isCurrentYear = $year === $currentYear;
        $currentMonthIndex = $isCurrentYear ? now()->month - 1 : null;
        $thisMonth = null;
        $changePercent = null;

        if ($currentMonthIndex !== null) {
            $months[$currentMonthIndex]['is_current'] = true;
            $thisMonth = $months[$currentMonthIndex];
            $previousTotal = $currentMonthIndex > 0
                ? $months[$currentMonthIndex - 1]['total']
                : $this->monthTotal($year - 1, 12);

            if ($previousTotal > 0) {
                $changePercent = round((($thisMonth['total'] - $previousTotal) / $previousTotal) * 100, 1);
            }
        }

        return [
            'year' => $year,
            'is_current_year' => $isCurrentYear,
            'months' => $months,
            'year_total' => $yearTotal,
            'year_rides' => $yearRides,
            'average_fare' => $yearRides > 0 ? round($yearTotal / $yearRides, 2) : 0.0,
            'max_total' => $bestTotal,
            'current_month_index' => $currentMonthIndex,
            'best_month_index' => $bestTotal > 0 ? $bestIndex : null,
            'this_month' => $thisMonth,
            'change_percent' => $changePercent,
            'available_years' => $this->availableYears($currentYear),
        ];
    }

    private function monthTotal(int $year, int $month): float
    {
        $total = DB::table('rides')
            ->where('status', RideStatus::Finshed->value)
            ->whereRaw($this->yearSql().' = ?', [$year])
            ->whereRaw($this->monthSql().' = ?', [$month])
            ->sum(DB::raw('COALESCE(calculated_final_price, 0)'));

        return round((float) $total, 2);
    }

    /**
     * @return list<int>
     */
    private function availableYears(int $currentYear): array
    {
        $years = DB::table('rides')
            ->where('status', RideStatus::Finshed->value)
            ->whereRaw($this->finishedAtSql().' IS NOT NULL')
            ->selectRaw($this->yearSql().' as year')
            ->distinct()
            ->pluck('year')
            ->map(fn ($year) => (int) $year)
            ->filter(fn (int $year) => $year >= 2000 && $year <= $currentYear)
            ->push($currentYear)
            ->unique()
            ->sortDesc()
            ->values()
            ->all();

        return array_map('intval', $years);
    }

    private function finishedAtSql(): string
    {
        return 'COALESCE(completed_at, ended_at, updated_at)';
    }

    private function yearSql(): string
    {
        $finishedAt = $this->finishedAtSql();

        return DB::getDriverName() === 'sqlite'
            ? "CAST(strftime('%Y', {$finishedAt}) AS INTEGER)"
            : "YEAR({$finishedAt})";
    }

    private function monthSql(): string
    {
        $finishedAt = $this->finishedAtSql();

        return DB::getDriverName() === 'sqlite'
            ? "CAST(strftime('%m', {$finishedAt}) AS INTEGER)"
            : "MONTH({$finishedAt})";
    }
}
