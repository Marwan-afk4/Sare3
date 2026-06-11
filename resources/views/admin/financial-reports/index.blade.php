@extends('layouts.app')
@php
    $currentPage = 'financial-reports';
@endphp
@section('title', __('Financial Reports'))
@section('content')
    <div class="container-fluid">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
            <h1 class="mb-0">{{ __('Financial Reports') }}</h1>
            <div class="btn-group" role="group">
                @foreach (['day' => __('Today'), 'week' => __('This Week'), 'month' => __('This Month'), 'year' => __('This Year')] as $periodKey => $periodLabel)
                    <a href="{{ route('financial-reports.index', array_merge(request()->except(['period', 'from_date', 'to_date']), ['tab' => $tab, 'period' => $periodKey])) }}"
                       class="btn btn-outline-primary btn-sm {{ ($filters['period'] ?? null) === $periodKey ? 'active' : '' }}">
                        {{ $periodLabel }}
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Summary Cards --}}
        <div class="row mb-4">
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card bg-primary text-white h-100">
                    <div class="card-body">
                        <h6 class="card-title">{{ __('Total Driver Wallet Balance') }}</h6>
                        <h3 class="mb-0">${{ number_format($stats->total_wallet_balance ?? 0, 2) }}</h3>
                        <small>{{ number_format($stats->total_drivers ?? 0) }} {{ __('Drivers') }}</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card bg-success text-white h-100">
                    <div class="card-body">
                        <h6 class="card-title">{{ __('Total Deposits') }}</h6>
                        <h3 class="mb-0">${{ number_format($stats->total_deposits ?? 0, 2) }}</h3>
                        <small>{{ number_format($stats->total_transactions ?? 0) }} {{ __('Transactions') }}</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card bg-danger text-white h-100">
                    <div class="card-body">
                        <h6 class="card-title">{{ __('Total Deductions') }}</h6>
                        <h3 class="mb-0">${{ number_format($stats->total_deductions ?? 0, 2) }}</h3>
                        <small>${{ number_format($stats->total_commissions ?? 0, 2) }} {{ __('Commissions') }}</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card bg-info text-white h-100">
                    <div class="card-body">
                        <h6 class="card-title">{{ __('Coupon Credits to Drivers') }}</h6>
                        <h3 class="mb-0">${{ number_format($stats->total_coupon_credits ?? 0, 2) }}</h3>
                        <small>{{ number_format($stats->total_coupon_usages ?? 0) }} {{ __('Coupon Usages') }}</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabs --}}
        <ul class="nav nav-tabs mb-3">
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'transactions' ? 'active' : '' }}"
                   href="{{ route('financial-reports.index', array_merge(request()->except('tab'), ['tab' => 'transactions'])) }}">
                    <i class="fa fa-exchange-alt me-1"></i> {{ __('Wallet Transactions') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'coupons' ? 'active' : '' }}"
                   href="{{ route('financial-reports.index', array_merge(request()->except('tab'), ['tab' => 'coupons'])) }}">
                    <i class="fa fa-tag me-1"></i> {{ __('Coupon Reports') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'wallets' ? 'active' : '' }}"
                   href="{{ route('financial-reports.index', array_merge(request()->except('tab'), ['tab' => 'wallets'])) }}">
                    <i class="fa fa-wallet me-1"></i> {{ __('Driver Wallets Overview') }}
                </a>
            </li>
        </ul>

        {{-- Filters --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fa fa-filter me-2"></i>{{ __('Filters') }}</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('financial-reports.index') }}" class="row g-3">
                    <input type="hidden" name="tab" value="{{ $tab }}">

                    <div class="col-md-3">
                        <label for="driver_id" class="form-label">{{ __('Driver') }}</label>
                        <select name="driver_id" id="driver_id" class="form-select driver-search-select">
                            <option value="">{{ __('All Drivers') }}</option>
                            @foreach ($drivers as $driver)
                                <option value="{{ $driver->id }}" {{ ($filters['driver_id'] ?? '') == $driver->id ? 'selected' : '' }}>
                                    {{ $driver->name }} ({{ $driver->phone }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if ($tab === 'transactions')
                        <div class="col-md-2">
                            <label for="type" class="form-label">{{ __('Type') }}</label>
                            <select name="type" id="type" class="form-select">
                                <option value="">{{ __('All Types') }}</option>
                                <option value="deposit" {{ ($filters['type'] ?? '') === 'deposit' ? 'selected' : '' }}>{{ __('Deposit') }}</option>
                                <option value="deduction" {{ ($filters['type'] ?? '') === 'deduction' ? 'selected' : '' }}>{{ __('Deduction') }}</option>
                                <option value="withdraw" {{ ($filters['type'] ?? '') === 'withdraw' ? 'selected' : '' }}>{{ __('Withdraw') }}</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label for="status" class="form-label">{{ __('Status') }}</label>
                            <select name="status" id="status" class="form-select">
                                <option value="">{{ __('All Statuses') }}</option>
                                <option value="approved" {{ ($filters['status'] ?? '') === 'approved' ? 'selected' : '' }}>{{ __('Approved') }}</option>
                                <option value="pending" {{ ($filters['status'] ?? '') === 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                                <option value="rejected" {{ ($filters['status'] ?? '') === 'rejected' ? 'selected' : '' }}>{{ __('Rejected') }}</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="category" class="form-label">{{ __('Transaction Category') }}</label>
                            <select name="category" id="category" class="form-select">
                                <option value="">{{ __('All Categories') }}</option>
                                <option value="coupon" {{ ($filters['category'] ?? '') === 'coupon' ? 'selected' : '' }}>{{ __('Coupon Benefits') }}</option>
                                <option value="commission" {{ ($filters['category'] ?? '') === 'commission' ? 'selected' : '' }}>{{ __('Admin Commissions') }}</option>
                                <option value="admin_adjustment" {{ ($filters['category'] ?? '') === 'admin_adjustment' ? 'selected' : '' }}>{{ __('Admin Adjustments') }}</option>
                                <option value="bonus" {{ ($filters['category'] ?? '') === 'bonus' ? 'selected' : '' }}>{{ __('Bonuses') }}</option>
                                <option value="withdraw_request" {{ ($filters['category'] ?? '') === 'withdraw_request' ? 'selected' : '' }}>{{ __('Withdrawal Requests') }}</option>
                            </select>
                        </div>
                    @endif

                    @if ($tab !== 'wallets')
                        <div class="col-md-2">
                            <label for="coupon_id" class="form-label">{{ __('Coupon') }}</label>
                            <select name="coupon_id" id="coupon_id" class="form-select">
                                <option value="">{{ __('All Coupons') }}</option>
                                @foreach ($coupons as $coupon)
                                    <option value="{{ $coupon->id }}" {{ ($filters['coupon_id'] ?? '') == $coupon->id ? 'selected' : '' }}>
                                        {{ $coupon->code }} - {{ $coupon->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    @if ($tab === 'wallets')
                        <div class="col-md-3">
                            <label for="balance_filter" class="form-label">{{ __('Wallet Balance') }}</label>
                            <select name="balance_filter" id="balance_filter" class="form-select">
                                <option value="">{{ __('All Balances') }}</option>
                                <option value="positive" {{ ($filters['balance_filter'] ?? '') === 'positive' ? 'selected' : '' }}>{{ __('Positive Balance') }}</option>
                                <option value="negative" {{ ($filters['balance_filter'] ?? '') === 'negative' ? 'selected' : '' }}>{{ __('Negative Balance') }}</option>
                                <option value="below_minimum" {{ ($filters['balance_filter'] ?? '') === 'below_minimum' ? 'selected' : '' }}>
                                    {{ __('Below Minimum') }} (${{ number_format($minimumBalance, 2) }})
                                </option>
                            </select>
                        </div>
                    @endif

                    <div class="col-md-2">
                        <label for="from_date" class="form-label">{{ __('From Date') }}</label>
                        <input type="date" name="from_date" id="from_date" class="form-control" value="{{ $filters['from_date'] ?? '' }}">
                    </div>

                    <div class="col-md-2">
                        <label for="to_date" class="form-label">{{ __('To Date') }}</label>
                        <input type="date" name="to_date" id="to_date" class="form-control" value="{{ $filters['to_date'] ?? '' }}">
                    </div>

                    <div class="col-md-3">
                        <label for="keyword" class="form-label">{{ __('Keyword') }}</label>
                        <input type="text" name="keyword" id="keyword" class="form-control"
                               placeholder="{{ __('Search in notes, name, phone...') }}"
                               value="{{ $filters['keyword'] ?? '' }}">
                    </div>

                    <div class="col-md-2">
                        <label for="per_page" class="form-label">{{ __('Per Page') }}</label>
                        <select name="per_page" id="per_page" class="form-select">
                            @foreach ([25, 50, 100] as $size)
                                <option value="{{ $size }}" {{ ($filters['per_page'] ?? 25) == $size ? 'selected' : '' }}>{{ $size }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-filter me-1"></i> {{ __('Apply Filters') }}
                        </button>
                        <a href="{{ route('financial-reports.index', ['tab' => $tab]) }}" class="btn btn-outline-secondary">
                            <i class="fa fa-times me-1"></i> {{ __('Clear Filters') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Tab Content --}}
        @if ($tab === 'transactions')
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fa fa-list me-2"></i>{{ __('Wallet Transaction History') }}
                        @if ($transactions)
                            <span class="badge bg-secondary ms-2">{{ $transactions->total() }}</span>
                        @endif
                    </h5>
                </div>
                <div class="card-body">
                    @if ($transactions && $transactions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ __('ID') }}</th>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Driver') }}</th>
                                        <th>{{ __('Type') }}</th>
                                        <th>{{ __('Amount') }}</th>
                                        <th>{{ __('Note') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($transactions as $transaction)
                                        <tr>
                                            <td>#{{ $transaction->id }}</td>
                                            <td>
                                                <div>{{ $transaction->created_at->format('M d, Y') }}</div>
                                                <small class="text-muted">{{ $transaction->created_at->format('h:i A') }}</small>
                                            </td>
                                            <td>
                                                @if ($transaction->driver)
                                                    <a href="{{ route('drivers.show', $transaction->driver) }}">{{ $transaction->driver->name }}</a>
                                                    <br><small class="text-muted">{{ $transaction->driver->phone }}</small>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>{!! $transaction->type->badge() !!}</td>
                                            <td>
                                                <span class="fw-bold {{ $transaction->type->value === 'deposit' ? 'text-success' : 'text-danger' }}">
                                                    {{ $transaction->type->value === 'deposit' ? '+' : '-' }}${{ number_format($transaction->amount, 2) }}
                                                </span>
                                            </td>
                                            <td><small>{{ $transaction->note ?? '-' }}</small></td>
                                            <td>{!! $transaction->status->badge() !!}</td>
                                            <td>
                                                <a href="{{ route('drivers.wallet-history', $transaction->driver_id) }}" class="btn btn-sm btn-outline-info">
                                                    <i class="fa fa-history"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">{{ $transactions->links('pagination::custom') }}</div>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="fa fa-wallet fa-3x mb-3"></i>
                            <p>{{ __('No wallet transactions found for the selected filters.') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        @elseif ($tab === 'coupons')
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fa fa-tag me-2"></i>{{ __('Coupon Usage & Driver Impact') }}
                        @if ($couponUsages)
                            <span class="badge bg-secondary ms-2">{{ $couponUsages->total() }}</span>
                        @endif
                    </h5>
                </div>
                <div class="card-body">
                    @if ($couponUsages && $couponUsages->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ __('ID') }}</th>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Coupon') }}</th>
                                        <th>{{ __('Passenger') }}</th>
                                        <th>{{ __('Driver') }}</th>
                                        <th>{{ __('Ride') }}</th>
                                        <th>{{ __('Discount Amount') }}</th>
                                        <th>{{ __('Ride Final Price') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($couponUsages as $usage)
                                        <tr>
                                            <td>#{{ $usage->id }}</td>
                                            <td>
                                                <div>{{ $usage->created_at->format('M d, Y') }}</div>
                                                <small class="text-muted">{{ $usage->created_at->format('h:i A') }}</small>
                                            </td>
                                            <td>
                                                <strong>{{ $usage->coupon->code ?? '-' }}</strong>
                                                <br><small class="text-muted">{{ $usage->coupon->name ?? '' }}</small>
                                            </td>
                                            <td>
                                                {{ $usage->user->name ?? '-' }}
                                                <br><small class="text-muted">{{ $usage->user->phone ?? '' }}</small>
                                            </td>
                                            <td>
                                                @if ($usage->ride?->driver)
                                                    <a href="{{ route('drivers.show', $usage->ride->driver) }}">{{ $usage->ride->driver->name }}</a>
                                                    <br><small class="text-muted">{{ $usage->ride->driver->phone }}</small>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                @if ($usage->ride)
                                                    <a href="{{ route('rides.show', $usage->ride) }}">#{{ $usage->ride->id }}</a>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                <span class="text-success fw-bold">${{ number_format($usage->discount_amount, 2) }}</span>
                                            </td>
                                            <td>
                                                @if ($usage->ride)
                                                    ${{ number_format($usage->ride->calculated_final_price ?? 0, 2) }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">{{ $couponUsages->links('pagination::custom') }}</div>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="fa fa-tag fa-3x mb-3"></i>
                            <p>{{ __('No coupon usages found for the selected filters.') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        @else
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fa fa-wallet me-2"></i>{{ __('Driver Wallets Overview') }}
                        @if ($driverWallets)
                            <span class="badge bg-secondary ms-2">{{ $driverWallets->total() }}</span>
                        @endif
                    </h5>
                    @if (($stats->negative_balance_count ?? 0) > 0)
                        <span class="badge bg-danger">{{ $stats->negative_balance_count }} {{ __('Negative Balance') }}</span>
                    @endif
                </div>
                <div class="card-body">
                    @if ($driverWallets && $driverWallets->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ __('Id') }}</th>
                                        <th>{{ __('Driver Name') }}</th>
                                        <th>{{ __('Phone') }}</th>
                                        <th>{{ __('Wallet Amount') }}</th>
                                        <th>{{ __('Total Transactions') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($driverWallets as $driver)
                                        <tr>
                                            <td>{{ $driver->id }}</td>
                                            <td>
                                                <a href="{{ route('drivers.show', $driver) }}">{{ $driver->name }}</a>
                                            </td>
                                            <td>{{ $driver->phone }}</td>
                                            <td>
                                                <span class="badge bg-{{ $driver->wallet >= $minimumBalance ? ($driver->wallet >= 0 ? 'success' : 'danger') : 'warning' }} rounded-pill px-3 py-2">
                                                    ${{ number_format($driver->wallet ?? 0, 2) }}
                                                </span>
                                            </td>
                                            <td>{{ $driver->wallet_requests_count }}</td>
                                            <td>
                                                <a href="{{ route('drivers.wallet-history', $driver) }}" class="btn btn-sm btn-info">
                                                    <i class="fa fa-history"></i> {{ __('History') }}
                                                </a>
                                                <a href="{{ route('financial-reports.index', ['tab' => 'transactions', 'driver_id' => $driver->id]) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fa fa-filter"></i> {{ __('Filter') }}
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">{{ $driverWallets->links('pagination::custom') }}</div>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="fa fa-users fa-3x mb-3"></i>
                            <p>{{ __('No drivers found for the selected filters.') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const driverSelect = document.querySelector('#driver_id');
            if (driverSelect && typeof Choices !== 'undefined') {
                new Choices(driverSelect, {
                    searchEnabled: true,
                    searchPlaceholderValue: @json(__('Search drivers...')),
                    noResultsText: @json(__('No results found')),
                    itemSelectText: '',
                    shouldSort: false,
                    allowHTML: false,
                });
            }
        });
    </script>
@endpush
