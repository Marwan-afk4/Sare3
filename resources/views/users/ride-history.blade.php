@extends('layouts.app')
@php
    $currentPage = 'users';
@endphp
@section('title', $user->name . ' - Ride History')
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1>{{ $user->name }} - {{ __('Ride History') }}</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('users.index') }}">{{ __('Users') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('users.show', $user) }}">{{ $user->name }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Ride History') }}</li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="{{ route('users.show', $user) }}" class="btn btn-secondary btn-sm">
                    <i class="fa fa-arrow-left"></i> {{ __('Back to Profile') }}
                </a>
            </div>
        </div>

        {{-- Statistics Cards --}}
        <div class="row mb-4">
            <div class="col-md-2">
                <div class="card text-center">
                    <div class="card-body">
                        <h4 class="text-primary">{{ $rideStatistics['total_rides'] }}</h4>
                        <small class="text-muted">{{ __('Total Rides') }}</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-center">
                    <div class="card-body">
                        <h4 class="text-success">{{ $rideStatistics['completed_rides'] }}</h4>
                        <small class="text-muted">{{ __('Completed') }}</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-center">
                    <div class="card-body">
                        <h4 class="text-danger">{{ $rideStatistics['cancelled_rides'] }}</h4>
                        <small class="text-muted">{{ __('Cancelled') }}</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-center">
                    <div class="card-body">
                        <h4 class="text-info">${{ number_format($rideStatistics['total_spent'], 2) }}</h4>
                        <small class="text-muted">{{ __('Total Spent') }}</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-center">
                    <div class="card-body">
                        <h4 class="text-warning">{{ number_format($rideStatistics['total_distance'], 1) }} km</h4>
                        <small class="text-muted">{{ __('Distance') }}</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-center">
                    <div class="card-body">
                        <h4 class="text-secondary">${{ number_format($rideStatistics['average_ride_cost'], 2) }}</h4>
                        <small class="text-muted">{{ __('Avg Cost') }}</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label for="status" class="form-label">{{ __('Status') }}</label>
                        <select name="status" id="status" class="form-select">
                            <option value="all" {{ $status === 'all' ? 'selected' : '' }}>{{ __('All Rides') }}</option>
                            <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>{{ __('Completed') }}</option>
                            <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>{{ __('Cancelled') }}</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="per_page" class="form-label">{{ __('Per Page') }}</label>
                        <select name="per_page" id="per_page" class="form-select">
                            <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                            <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">{{ __('Filter') }}</button>
                        <a href="{{ route('users.ride-history', $user) }}" class="btn btn-outline-secondary ms-2">{{ __('Reset') }}</a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Rides Table --}}
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">{{ __('Ride History') }} ({{ $rides->total() }} {{ __('rides') }})</h5>
            </div>
            <div class="card-body">
                @if($rides->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('Ride ID') }}</th>
                                    <th>{{ __('Driver') }}</th>
                                    <th>{{ __('Car') }}</th>
                                    <th>{{ __('Route') }}</th>
                                    <th>{{ __('Distance') }}</th>
                                    <th>{{ __('Duration') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Price') }}</th>
                                    <th>{{ __('Date') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ridesData as $ride)
                                <tr>
                                    <td>
                                        <strong>#{{ $ride['ride_id'] }}</strong>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($ride['driver']['driver_image_link'])
                                                <img src="{{ $ride['driver']['driver_image_link'] }}"
                                                     alt="{{ $ride['driver']['driver_name'] }}"
                                                     class="rounded-circle me-2" width="40" height="40">
                                            @else
                                                <div class="bg-secondary rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                     style="width: 40px; height: 40px;">
                                                    <i class="fa fa-user text-white"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <div class="fw-bold">{{ $ride['driver']['driver_name'] }}</div>
                                                <small class="text-muted">{{ $ride['driver']['driver_phone'] }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($ride['car']['car_model'])
                                            <div>
                                                <div class="fw-bold">{{ $ride['car']['car_model'] }}</div>
                                                <small class="text-muted">{{ $ride['car']['car_number'] }}</small>
                                                @if($ride['car']['car_color'])
                                                    <br><small class="text-muted">{{ $ride['car']['car_color'] }}</small>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-muted">{{ __('N/A') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div>
                                            <small class="text-success">
                                                <i class="fa fa-circle text-success"></i> {{ Str::limit($ride['pickup_address'], 40) }}
                                            </small><br>
                                            <small class="text-danger">
                                                <i class="fa fa-map-marker-alt text-danger"></i> {{ Str::limit($ride['dropoff_address'], 40) }}
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        @if($ride['total_distance_in_km'])
                                            {{ number_format($ride['total_distance_in_km'], 1) }} km
                                        @else
                                            <span class="text-muted">{{ __('N/A') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($ride['time_taken'])
                                            {{ $ride['time_taken'] }}
                                        @elseif($ride['started_at'] && $ride['ended_at'])
                                            {{ \Carbon\Carbon::parse($ride['started_at'])->diffForHumans(\Carbon\Carbon::parse($ride['ended_at']), true) }}
                                        @else
                                            <span class="text-muted">{{ __('N/A') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $status = ucfirst(strtolower($ride['status'])); // normalize value

                                            $statusClass = match($status) {
                                                'Completed', 'Finshed' => 'success',
                                                'Cancelled' , 'Rejected' => 'danger',
                                                'In_progress' , 'Waiting_user' => 'warning',
                                                'Accepted' => 'info',
                                                default => 'secondary',
                                            };
                                        @endphp

                                        <span class="badge bg-{{ $statusClass }}">
                                            {{ __(ucfirst(str_replace('_', ' ', $ride['status']))) }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong>${{ number_format($ride['calculated_final_price'], 2) }}</strong>
                                    </td>
                                    <td>
                                        <div>
                                            {{ \Carbon\Carbon::parse($ride['created_at'])->format('M d, Y') }}<br>
                                            <small class="text-muted">{{ \Carbon\Carbon::parse($ride['created_at'])->format('H:i') }}</small>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    {{-- <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            {{ __('Showing') }} {{ $rides->firstItem() }} {{ __('to') }} {{ $rides->lastItem() }}
                            {{ __('of') }} {{ $rides->total() }} {{ __('rides') }}
                        </div>
                        <div>
                            {{ $users->links('pagination::custom') }}
                        </div>
                    </div> --}}
                    {{ $rides->links('pagination::custom') }}
                @else
                    <div class="text-center py-5">
                        <i class="fa fa-car fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">{{ __('No rides found') }}</h5>
                        <p class="text-muted">{{ __('This user has not taken any rides yet.') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
