@extends('layouts.app')
@php
    $currentPage = 'referrals';
@endphp
@section('title', __('Referral Management'))
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">{{ __('Referral Management') }}</h1>
            <div class="btn-group">
                <a href="{{ route('referrals.list') }}" class="btn btn-outline-primary">
                    <i class="fas fa-list me-2"></i>{{ __('View All Referrals') }}
                </a>
                <a href="{{ route('referrals.settings') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-cog me-2"></i>{{ __('Settings') }}
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    {{ __('Total Referrals') }}
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalReferrals) }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-users fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    {{ __('Active Referrals') }}
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($activeReferrals) }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-user-check fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    {{ __('Total Discounts Given') }}
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">${{ number_format($totalDiscounts, 2) }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    {{ __('Rides with Discounts') }}
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalRidesWithDiscount) }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-car fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Top Referrers -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-trophy me-2"></i>{{ __('Top Referrers') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        @if($topReferrers->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Rank') }}</th>
                                            <th>{{ __('Name') }}</th>
                                            <th>{{ __('Type') }}</th>
                                            <th>{{ __('Referrals') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($topReferrers as $index => $referrer)
                                            <tr>
                                                <td>
                                                    @if($index === 0)
                                                        <i class="fas fa-crown text-warning"></i>
                                                    @else
                                                        {{ $index + 1 }}
                                                    @endif
                                                </td>
                                                <td>
                                                    <strong>{{ $referrer->referrer->name }}</strong>
                                                    <br><small class="text-muted">{{ $referrer->referrer->phone }}</small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $referrer->referrer->role === 'driver' ? 'primary' : 'success' }}">
                                                        {{ ucfirst($referrer->referrer->role) }}
                                                    </span>
                                                </td>
                                                <td><strong>{{ $referrer->referral_count }}</strong></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-users fa-3x text-gray-300 mb-3"></i>
                                <p class="text-muted">{{ __('No referrals found yet') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Recent Referrals -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-clock me-2"></i>{{ __('Recent Referrals') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        @if($recentReferrals->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Referrer') }}</th>
                                            <th>{{ __('New User') }}</th>
                                            <th>{{ __('Date') }}</th>
                                            <th>{{ __('Status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recentReferrals as $referral)
                                            <tr>
                                                <td>
                                                    <strong>{{ $referral->referrer->name }}</strong>
                                                    <br><small class="text-muted">{{ $referral->referrer->phone }}</small>
                                                </td>
                                                <td>
                                                    <strong>{{ $referral->referredUser->name }}</strong>
                                                    <br><small class="text-muted">{{ $referral->referredUser->phone }}</small>
                                                </td>
                                                <td>{{ $referral->accepted_at ? $referral->accepted_at->format('M d, Y') : 'N/A' }}</td>
                                                <td>
                                                    @if($referral->is_active && $referral->used_rides_count < $referral->discount_rides_count)
                                                        <span class="badge bg-success">{{ __('Active') }}</span>
                                                    @else
                                                        <span class="badge bg-secondary">{{ __('Completed') }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-center mt-3">
                                <a href="{{ route('referrals.list') }}" class="btn btn-sm btn-outline-primary">
                                    {{ __('View All Referrals') }}
                                </a>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-handshake fa-3x text-gray-300 mb-3"></i>
                                <p class="text-muted">{{ __('No recent referrals found') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Discount Breakdown -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-chart-pie me-2"></i>{{ __('Discount Breakdown') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-6">
                                <div class="border-end">
                                    <h4 class="text-success">${{ number_format($totalReferralDiscounts, 2) }}</h4>
                                    <p class="text-muted mb-0">{{ __('New User Discounts') }}</p>
                                    <small class="text-muted">{{ __('Discounts given to referred users') }}</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h4 class="text-primary">${{ number_format($totalReferrerRewards, 2) }}</h4>
                                <p class="text-muted mb-0">{{ __('Referrer Rewards') }}</p>
                                <small class="text-muted">{{ __('Rewards given to referrers') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .border-left-primary {
            border-left: 0.25rem solid #4e73df !important;
        }
        .border-left-success {
            border-left: 0.25rem solid #1cc88a !important;
        }
        .border-left-info {
            border-left: 0.25rem solid #36b9cc !important;
        }
        .border-left-warning {
            border-left: 0.25rem solid #f6c23e !important;
        }
    </style>
@endsection