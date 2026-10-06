<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GoogleApiUsageService
{
    public static function record(string $sku, int $units = 1, int $requests = 1): void
    {
        if (! isset(config('google_api_pricing.skus')[$sku])) {
            return;
        }

        $units = max(0, $units);
        $requests = max(0, $requests);

        if ($units === 0 && $requests === 0) {
            return;
        }

        try {
            $date = now(config('google_api_pricing.timezone'))->toDateString();
            $now = now();
            $driver = DB::connection()->getDriverName();

            if (in_array($driver, ['mysql', 'mariadb'], true)) {
                DB::statement(
                    'INSERT INTO google_api_usages (usage_date, sku, requests, units, created_at, updated_at)
                     VALUES (?, ?, ?, ?, ?, ?)
                     ON DUPLICATE KEY UPDATE
                        requests = requests + VALUES(requests),
                        units = units + VALUES(units),
                        updated_at = VALUES(updated_at)',
                    [$date, $sku, $requests, $units, $now, $now]
                );

                return;
            }

            $existing = DB::table('google_api_usages')
                ->where('usage_date', $date)
                ->where('sku', $sku)
                ->first();

            if ($existing) {
                DB::table('google_api_usages')->where('id', $existing->id)->update([
                    'requests' => $existing->requests + $requests,
                    'units' => $existing->units + $units,
                    'updated_at' => $now,
                ]);
            } else {
                DB::table('google_api_usages')->insert([
                    'usage_date' => $date,
                    'sku' => $sku,
                    'requests' => $requests,
                    'units' => $units,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Google API usage was not recorded: '.$e->getMessage());
        }
    }

    public static function recordDistanceMatrix(int $origins, int $destinations = 1): void
    {
        $elements = max(0, $origins) * max(1, $destinations);

        if ($elements < 1) {
            return;
        }

        self::record('distance_matrix', $elements, 1);
    }

    public static function reportForMonth(Carbon $month): array
    {
        $timezone = config('google_api_pricing.timezone');
        $start = $month->copy()->timezone($timezone)->startOfMonth()->toDateString();
        $end = $month->copy()->timezone($timezone)->endOfMonth()->toDateString();

        $totals = DB::table('google_api_usages')
            ->select('sku', DB::raw('SUM(requests) as requests'), DB::raw('SUM(units) as units'))
            ->whereBetween('usage_date', [$start, $end])
            ->groupBy('sku')
            ->get()
            ->keyBy('sku');

        $daily = DB::table('google_api_usages')
            ->whereBetween('usage_date', [$start, $end])
            ->orderBy('usage_date')
            ->get()
            ->groupBy(fn ($row) => (string) $row->usage_date);

        $rows = [];
        $totalCost = 0.0;

        foreach (config('google_api_pricing.skus') as $sku => $definition) {
            $usedUnits = (int) ($totals[$sku]->units ?? 0);
            $requests = (int) ($totals[$sku]->requests ?? 0);
            $cost = self::costFor($usedUnits, $definition);
            $totalCost += $cost['amount'];

            $rows[] = [
                'sku' => $sku,
                'name' => $definition['name'],
                'sku_id' => $definition['sku_id'],
                'category' => $definition['category'],
                'unit' => $definition['unit'],
                'used_for' => $definition['used_for'],
                'requests' => $requests,
                'used' => $usedUnits,
                'free_cap' => $definition['free_cap'],
                'unlimited' => (bool) ($definition['unlimited'] ?? false),
                'remaining_free' => $cost['remaining_free'],
                'overage' => $cost['overage'],
                'estimated_cost' => $cost['amount'],
                'price_after_free' => $cost['price_after_free'],
                'percent' => $cost['percent'],
            ];
        }

        $firstTracked = DB::table('google_api_usages')->min('usage_date');

        return [
            'month' => $month->timezone($timezone)->format('Y-m'),
            'label' => $month->timezone($timezone)->translatedFormat('F Y'),
            'start' => $start,
            'end' => $end,
            'rows' => $rows,
            'total_cost' => $totalCost,
            'first_tracked' => $firstTracked,
            'daily' => $daily,
        ];
    }

    public static function costFor(int $units, array $definition): array
    {
        if ($definition['unlimited'] ?? false) {
            return [
                'amount' => 0.0,
                'overage' => 0,
                'remaining_free' => null,
                'percent' => null,
                'price_after_free' => __('No charge'),
            ];
        }

        $freeCap = (int) $definition['free_cap'];
        $overage = max(0, $units - $freeCap);
        $remaining = max(0, $freeCap - $units);
        $cursor = min($units, $freeCap);
        $billableLeft = $overage;
        $amount = 0.0;
        $firstPaidRate = $definition['tiers'][0]['per_thousand'] ?? 0;

        foreach ($definition['tiers'] as $tier) {
            if ($billableLeft <= 0) {
                break;
            }

            $tierEnd = $tier['up_to'];
            $room = $tierEnd === null ? $billableLeft : max(0, $tierEnd - $cursor);
            $take = min($billableLeft, $room);

            $amount += ($take / 1000) * $tier['per_thousand'];
            $billableLeft -= $take;
            $cursor += $take;
        }

        return [
            'amount' => round($amount, 2),
            'overage' => $overage,
            'remaining_free' => $remaining,
            'percent' => $freeCap > 0 ? min(100, round(($units / $freeCap) * 100, 1)) : 0,
            'price_after_free' => '$'.number_format($firstPaidRate, 2).' / 1,000',
        ];
    }
}
