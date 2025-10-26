@extends('layouts.app')
@php
    $currentPage = 'profit-statistics';
@endphp
@section('title', __('Profit Statistics'))
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1>{{ __('Profit Statistics') }}</h1>
            <div class="btn-group" role="group">
                <a href="{{ route('profit-statistics.index', array_merge(['period' => 'day'], request()->only('zone'))) }}"
                   class="btn btn-outline-primary {{ $period === 'day' ? 'active' : '' }}">{{ __('Today') }}</a>
                <a href="{{ route('profit-statistics.index', array_merge(['period' => 'week'], request()->only('zone'))) }}"
                   class="btn btn-outline-primary {{ $period === 'week' ? 'active' : '' }}">{{ __('This Week') }}</a>
                <a href="{{ route('profit-statistics.index', array_merge(['period' => 'month'], request()->only('zone'))) }}"
                   class="btn btn-outline-primary {{ $period === 'month' ? 'active' : '' }}">{{ __('This Month') }}</a>
                <a href="{{ route('profit-statistics.index', array_merge(['period' => 'year'], request()->only('zone'))) }}"
                   class="btn btn-outline-primary {{ $period === 'year' ? 'active' : '' }}">{{ __('This Year') }}</a>
            </div>
        </div>

        {{-- Zone Filter Buttons --}}
        <div class="mb-3">
            <label class="form-label fw-bold">{{ __('Filter by Zone') }}</label>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('profit-statistics.index', array_merge(['period' => $period])) }}" 
                   class="btn btn-outline-info btn-sm {{ !request('zone') ? 'active' : '' }}">
                    {{ __('All Zones') }}
                </a>
                @foreach ($zones as $zone)
                    <a href="{{ route('profit-statistics.index', array_merge(['period' => $period], ['zone' => $zone->id])) }}" 
                        class="btn btn-sm {{ request('zone') == $zone->id ? 'btn-info' : 'btn-outline-info' }}">
                        {{ $zone->name }} ({{ $zone->profit_rides_count }})
                    </a>
                @endforeach
                <a href="{{ route('profit-statistics.index', array_merge(['period' => $period], ['zone' => 'no_zone'])) }}" 
                    class="btn btn-sm {{ request('zone') === 'no_zone' ? 'btn-warning' : 'btn-outline-warning' }}">
                    {{ __('No Zone') }} ({{ $profitsWithNoZoneCount }})
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">{{ __('Total Rides') }}</h6>
                                <h3 class="mb-0">{{ number_format($currentStats->total_rides ?? 0) }}</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-car fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">{{ __('Total Fare') }}</h6>
                                <h3 class="mb-0">${{ number_format($currentStats->total_fare ?? 0, 2) }}</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-dollar-sign fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">{{ __('Admin Profit') }}</h6>
                                <h3 class="mb-0">${{ number_format($currentStats->total_admin_profit ?? 0, 2) }}</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-chart-line fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">{{ __('Avg Profit %') }}</h6>
                                <h3 class="mb-0">{{ number_format($currentStats->avg_profit_percentage ?? 0, 1) }}%</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-percentage fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Daily Profit Chart -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Daily Profit Breakdown') }}</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="profitChart" height="100"></canvas>
                    </div>
                </div>
            </div>

            <!-- Top Drivers -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Top Earning Drivers') }}</h5>
                    </div>
                    <div class="card-body">
                        @if($topDrivers->count() > 0)
                            <div class="list-group list-group-flush">
                                @foreach($topDrivers as $driver)
                                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <div>
                                            <h6 class="mb-1">{{ $driver->driver->name ?? 'Driver #' . $driver->driver_id }}</h6>
                                            <small class="text-muted">{{ $driver->total_rides }} {{ __('Rides') }}</small>
                                        </div>
                                        <div class="text-end">
                                            <strong>${{ number_format($driver->total_driver_earnings, 2) }}</strong>
                                            <br>
                                            <small class="text-muted">${{ number_format($driver->total_admin_profit, 2) }} {{ __('Profit') }}</small>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted text-center">{{ __('No data available for this period.') }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center">
                        <a href="{{ route('profit-statistics.history') }}" class="btn btn-primary me-2">
                            <i class="fas fa-history"></i> {{ __('View Profit History') }}
                        </a>
                        <a href="{{ route('settings.index') }}" class="btn btn-secondary">
                            <i class="fas fa-cog"></i> {{ __('Manage Settings') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Profit Chart
        const ctx = document.getElementById('profitChart').getContext('2d');
        const chartData = @json($dailyBreakdown);

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.map(item => item.date),
                datasets: [{
                    label: 'Daily Profit ($)',
                    data: chartData.map(item => parseFloat(item.daily_profit)),
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toFixed(2);
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Profit: $' + context.parsed.y.toFixed(2);
                            }
                        }
                    }
                }
            }
        });
    </script>
@endsection
