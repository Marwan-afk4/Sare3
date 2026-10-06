<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\GoogleApiUsageService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class GoogleApiUsageController extends Controller
{
    public function index(Request $request)
    {
        $timezone = config('google_api_pricing.timezone');
        $month = $request->query('month');

        try {
            $selected = $month
                ? Carbon::createFromFormat('Y-m', $month, $timezone)->startOfMonth()
                : now($timezone)->startOfMonth();
        } catch (\Throwable $e) {
            $selected = now($timezone)->startOfMonth();
        }

        $report = GoogleApiUsageService::reportForMonth($selected);

        return view('admin.google-api-usage.index', [
            'currentPage' => 'google-api-usage',
            'report' => $report,
            'previousMonth' => $selected->copy()->subMonth()->format('Y-m'),
            'nextMonth' => $selected->copy()->addMonth()->format('Y-m'),
            'isCurrentMonth' => $selected->isSameMonth(now($timezone)),
        ]);
    }

    public function record(Request $request)
    {
        $allowed = collect(config('google_api_pricing.skus'))
            ->filter(fn ($sku) => $sku['client'] ?? false)
            ->keys()
            ->all();

        $data = $request->validate([
            'events' => ['required', 'array', 'max:10'],
            'events.*' => ['string', 'in:'.implode(',', $allowed)],
        ]);

        foreach (array_unique($data['events']) as $sku) {
            GoogleApiUsageService::record($sku, 1, 1);
        }

        return response()->noContent();
    }
}
