<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\User;
use App\Models\WalletRequest;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class FinancialReportsWebController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'transactions');
        $filters = $this->extractFilters($request);

        $drivers = User::where('role', 'driver')
            ->orderBy('name')
            ->get(['id', 'name', 'phone', 'wallet']);

        $coupons = Coupon::orderBy('name')->get(['id', 'code', 'name']);

        $minimumBalance = AppSetting::getMinimumDriverWalletBalance();

        $stats = $this->getSummaryStats($filters);

        $transactions = null;
        $couponUsages = null;
        $driverWallets = null;

        if ($tab === 'coupons') {
            $couponUsages = $this->getCouponUsages($filters);
        } elseif ($tab === 'wallets') {
            $driverWallets = $this->getDriverWallets($filters, $minimumBalance);
        } else {
            $transactions = $this->getTransactions($filters);
        }

        return view('admin.financial-reports.index', compact(
            'tab',
            'filters',
            'drivers',
            'coupons',
            'minimumBalance',
            'stats',
            'transactions',
            'couponUsages',
            'driverWallets'
        ));
    }

    private function extractFilters(Request $request): array
    {
        return [
            'driver_id' => $request->get('driver_id'),
            'type' => $request->get('type'),
            'status' => $request->get('status'),
            'category' => $request->get('category'),
            'coupon_id' => $request->get('coupon_id'),
            'from_date' => $request->get('from_date'),
            'to_date' => $request->get('to_date'),
            'keyword' => $request->get('keyword'),
            'balance_filter' => $request->get('balance_filter'),
            'period' => $request->get('period'),
            'per_page' => (int) $request->get('per_page', 25),
        ];
    }

    private function getSummaryStats(array $filters): object
    {
        $query = WalletRequest::query();
        $this->applyTransactionFilters($query, $filters, applyDriverFilter: true);

        $stats = $query->selectRaw("
            COUNT(*) as total_transactions,
            COALESCE(SUM(CASE WHEN type = 'deposit' THEN amount ELSE 0 END), 0) as total_deposits,
            COALESCE(SUM(CASE WHEN type = 'deduction' THEN amount ELSE 0 END), 0) as total_deductions,
            COALESCE(SUM(CASE WHEN type = 'withdraw' THEN amount ELSE 0 END), 0) as total_withdrawals,
            COALESCE(SUM(CASE WHEN note LIKE '%Coupon benefit%' THEN amount ELSE 0 END), 0) as total_coupon_credits,
            COALESCE(SUM(CASE WHEN note LIKE '%Admin profit commission%' THEN amount ELSE 0 END), 0) as total_commissions
        ")->first();

        $driverQuery = User::where('role', 'driver');
        $this->applyDriverWalletFilters($driverQuery, $filters, AppSetting::getMinimumDriverWalletBalance());

        $walletStats = (clone $driverQuery)->selectRaw('
            COUNT(*) as total_drivers,
            COALESCE(SUM(wallet), 0) as total_wallet_balance,
            SUM(CASE WHEN wallet < 0 THEN 1 ELSE 0 END) as negative_balance_count
        ')->first();

        $couponStatsQuery = CouponUsage::query();
        $this->applyCouponUsageFilters($couponStatsQuery, $filters);

        $couponStats = $couponStatsQuery->selectRaw('
            COUNT(*) as total_coupon_usages,
            COALESCE(SUM(discount_amount), 0) as total_coupon_discount
        ')->first();

        return (object) array_merge(
            $stats ? $stats->toArray() : [],
            $walletStats ? $walletStats->toArray() : [],
            $couponStats ? $couponStats->toArray() : []
        );
    }

    private function getTransactions(array $filters)
    {
        $query = WalletRequest::with('driver:id,name,phone,wallet')
            ->orderByDesc('created_at');

        $this->applyTransactionFilters($query, $filters);

        return $query->paginate($filters['per_page'])->withQueryString();
    }

    private function getCouponUsages(array $filters)
    {
        $query = CouponUsage::with([
            'coupon:id,code,name,type,value',
            'user:id,name,phone',
            'ride:id,driver_id,coupon_discount,calculated_final_price,status',
            'ride.driver:id,name,phone',
        ])->orderByDesc('created_at');

        $this->applyCouponUsageFilters($query, $filters);

        return $query->paginate($filters['per_page'])->withQueryString();
    }

    private function getDriverWallets(array $filters, float $minimumBalance)
    {
        $query = User::where('role', 'driver')
            ->withCount('walletRequests')
            ->orderByDesc('wallet');

        $this->applyDriverWalletFilters($query, $filters, $minimumBalance);

        if ($filters['keyword']) {
            $keyword = $filters['keyword'];
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'LIKE', "%{$keyword}%")
                    ->orWhere('phone', 'LIKE', "%{$keyword}%")
                    ->orWhere('email', 'LIKE', "%{$keyword}%");
            });
        }

        return $query->paginate($filters['per_page'])->withQueryString();
    }

    private function applyTransactionFilters(Builder $query, array $filters, bool $applyDriverFilter = true): void
    {
        if ($applyDriverFilter && $filters['driver_id']) {
            $query->where('driver_id', $filters['driver_id']);
        }

        if ($filters['type']) {
            $query->where('type', $filters['type']);
        }

        if ($filters['status']) {
            $query->where('status', $filters['status']);
        }

        if ($filters['keyword']) {
            $keyword = $filters['keyword'];
            $query->where('note', 'LIKE', "%{$keyword}%");
        }

        $this->applyCategoryFilter($query, $filters['category']);

        if ($filters['coupon_id']) {
            $rideIds = CouponUsage::where('coupon_id', $filters['coupon_id'])->pluck('ride_id');
            $query->where(function ($q) use ($rideIds) {
                $q->where('note', 'LIKE', '%Coupon benefit%');
                if ($rideIds->isNotEmpty()) {
                    foreach ($rideIds as $rideId) {
                        $q->orWhere('note', 'LIKE', "%ride #{$rideId}%");
                    }
                }
            });
        }

        $this->applyDateFilters($query, $filters);
    }

    private function applyCategoryFilter(Builder $query, ?string $category): void
    {
        if (!$category) {
            return;
        }

        match ($category) {
            'coupon' => $query->where('note', 'LIKE', '%Coupon benefit%'),
            'commission' => $query->where('note', 'LIKE', '%Admin profit commission%'),
            'admin_adjustment' => $query->where(function ($q) {
                $q->where('note', 'LIKE', '%Admin added%')
                    ->orWhere('note', 'LIKE', '%Admin deducted%');
            }),
            'bonus' => $query->where(function ($q) {
                $q->where('note', 'LIKE', '%Milestone bonus%')
                    ->orWhere('note', 'LIKE', '%Manual bonus%')
                    ->orWhere('note', 'LIKE', '%bonus%');
            }),
            'withdraw_request' => $query->where('type', 'withdraw'),
            default => null,
        };
    }

    private function applyCouponUsageFilters(Builder $query, array $filters): void
    {
        if ($filters['coupon_id']) {
            $query->where('coupon_id', $filters['coupon_id']);
        }

        if ($filters['driver_id']) {
            $query->whereHas('ride', function ($q) use ($filters) {
                $q->where('driver_id', $filters['driver_id']);
            });
        }

        if ($filters['from_date']) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if ($filters['to_date']) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        if ($filters['period']) {
            $this->applyPeriodToQuery($query, $filters['period']);
        }
    }

    private function applyDriverWalletFilters(Builder $query, array $filters, float $minimumBalance): void
    {
        if ($filters['driver_id']) {
            $query->where('id', $filters['driver_id']);
        }

        match ($filters['balance_filter']) {
            'positive' => $query->where('wallet', '>=', 0),
            'negative' => $query->where('wallet', '<', 0),
            'below_minimum' => $query->where('wallet', '<', $minimumBalance),
            default => null,
        };
    }

    private function applyDateFilters(Builder $query, array $filters): void
    {
        if ($filters['from_date']) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if ($filters['to_date']) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        if ($filters['period'] && !$filters['from_date'] && !$filters['to_date']) {
            $this->applyPeriodToQuery($query, $filters['period']);
        }
    }

    private function applyPeriodToQuery(Builder $query, string $period): void
    {
        match ($period) {
            'day' => $query->whereDate('created_at', Carbon::today()),
            'week' => $query->whereBetween('created_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek(),
            ]),
            'month' => $query->whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year),
            'year' => $query->whereYear('created_at', Carbon::now()->year),
            default => null,
        };
    }
}
