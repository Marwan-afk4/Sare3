@extends('layouts.app')
@php
    $currentPage = 'profit-history';
@endphp
@section('title', __('Profit History'))
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1>{{ __('Profit History') }}</h1>
            <a href="{{ route('profit-statistics.index') }}" class="btn btn-secondary">
                <i class="fas fa-chart-bar"></i> {{ __('Back to Statistics') }}
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                @if($profits->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Driver') }}</th>
                                    <th>{{ __('Ride') }}</th>
                                    <th>{{ __('Total Fare') }}</th>
                                    <th>{{ __('Profit %') }}</th>
                                    <th>{{ __('Admin Profit') }}</th>
                                    <th>{{ __('Driver Amount') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($profits as $profit)
                                    <tr>
                                        <td>{{ $profit->processed_at->format('M d, Y H:i') }}</td>
                                        <td>
                                            <div>
                                                <strong>{{ $profit->driver->name ?? 'Driver #' . $profit->driver_id }}</strong>
                                                @if($profit->driver->phone)
                                                    <br><small class="text-muted">{{ $profit->driver->phone }}</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <small class="text-muted">
                                                    {{ __('From:') }} {{ Str::limit($profit->ride->pickup_address ?? 'N/A', 30) }}<br>
                                                    {{ __('To:') }} {{ Str::limit($profit->ride->dropoff_address ?? 'N/A', 30) }}
                                                </small>
                                            </div>
                                        </td>
                                        <td>
                                            <strong>${{ number_format($profit->total_fare, 2) }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ number_format($profit->admin_profit_percentage, 1) }}%</span>
                                        </td>
                                        <td>
                                            <strong class="text-success">${{ number_format($profit->admin_profit_amount, 2) }}</strong>
                                        </td>
                                        <td>
                                            <strong class="text-primary">${{ number_format($profit->driver_amount, 2) }}</strong>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $profits->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">{{ __('No Profit Records Found') }}</h4>
                        <p class="text-muted">{{ __('Profit records will appear here once rides are completed with admin profit enabled.') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
