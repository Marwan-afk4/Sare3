@extends('layouts.app')
@php
    $currentPage = 'drivers';
@endphp
@section('title', $driver->name)
@section('content')
    <div class="container-fluid">
        <h1>{{ $driver->name }}</h1>
        <div class="mb-3">
            <a href="{{ route('drivers.index') }}" class="btn btn-secondary btn-sm me-1">
                <i class="fa fa-arrow-right"></i> {{ __('Back to') }} {{ __('Drivers') }}
            </a>
            {{-- <a href='{{ route('drivers.edit', $driver) }}' class="btn btn-warning btn-sm me-1">
                {{ __('Edit') }} <i class="fa fa-edit"></i>
            </a> --}}
        </div>

        <div class="card">
            <div class="card-body">
                <div class="row">
                    {{-- Profile Image --}}
                    <div class="col-md-2 text-center">
                        @if ($driver->image)
                            <img src="{{ $driver->image_link ?? 'https://th.bing.com/th/id/OIP.hGSCbXlcOjL_9mmzerqAbQHaHa?rs=1&pid=ImgDetMain' }}"
                                alt="{{ $driver->name }}" class="img-thumbnail mb-3" style="max-width: 100%;">
                        @else
                            <img src="https://th.bing.com/th/id/OIP.hGSCbXlcOjL_9mmzerqAbQHaHa?rs=1&pid=ImgDetMain"
                                alt="No Image" class="img-thumbnail mb-3">
                        @endif
                    </div>

                    {{-- Info --}}
                    <div class="col-md-9">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item"><strong>{{ __('Id') }}:</strong> {{ $driver->id }}</li>
                            <li class="list-group-item"><strong>{{ __('Name') }}:</strong> {{ $driver->name }}</li>
                            <li class="list-group-item"><strong>{{ __('Email') }}:</strong> {{ $driver->email }}</li>
                            <li class="list-group-item"><strong>{{ __('Phone') }}:</strong> {{ $driver->phone }}</li>
                            <li class="list-group-item"><strong>{{ __('Status') }}:</strong>
                                {!! $driver->status->badge() !!}
                            </li>
                            <li class="list-group-item"><strong>{{ __('Activity') }}:</strong>
                                {!! $driver->activity->badge() !!}
                            </li>
                            <li class="list-group-item"><strong>{{ __('Wallet') }}:</strong>
                                @if($driver->wallet < 0)
                                    <span style="color: red;">{{ $driver->wallet }} 🔴</span>
                                @else
                                    {{ $driver->wallet ?? '-' }}
                                @endif
                            </li>
                            <li class="list-group-item"><strong>{{ __('Driver Rating') }}:</strong>
                                @if ($driverRating)
                                    <span class="badge bg-warning">{{ number_format($driverRating, 1) }} ⭐</span>
                                @else
                                    <span class="text-muted">{{ __('No ratings yet') }}</span>
                                @endif
                            </li>
                            <li class="list-group-item"><strong>{{ __('Created At') }}:</strong>
                                {{ $driver->created_at?->diffForHumans() }}</li>
                            <li class="list-group-item"><strong>{{ __('Updated At') }}:</strong>
                                {{ $driver->updated_at?->diffForHumans() }}</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <a href='{{ route('drivers.edit', $driver) }}'
                    class="btn btn-subtle-warning btn-sm me-1">{{ __('Edit') }} <i class="fa fa-edit"></i></a>
                <a href="{{ route('drivers.documents', $driver->id) }}" class="btn btn-subtle-info btn-sm me-1">
                    {{ __('Documents') }} <i class="fa fa-file-alt"></i>
                </a>
                <a href="{{ route('drivers.cars', $driver->id) }}" class="btn btn-subtle-info btn-sm me-1">
                    {{ __('Cars') }} <i class="fa fa-car"></i>
                </a>
                <a href="{{ route('drivers.ride-history', $driver->id) }}" class="btn btn-subtle-info btn-sm me-1">
                    {{ __('Ride History') }} <i class="fa fa-history"></i>
                </a>
            </div>
        </div>

        {{-- Ride Statistics Card --}}
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">{{ __('Ride Statistics') }}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2">
                        <div class="text-center">
                            <h4 class="text-primary">{{ $rideStatistics['total_rides'] }}</h4>
                            <small class="text-muted">{{ __('Total Rides') }}</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-center">
                            <h4 class="text-success">{{ $rideStatistics['completed_rides'] }}</h4>
                            <small class="text-muted">{{ __('Completed') }}</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-center">
                            <h4 class="text-danger">{{ $rideStatistics['cancelled_rides'] }}</h4>
                            <small class="text-muted">{{ __('Cancelled') }}</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-center">
                            <h4 class="text-info">${{ number_format($rideStatistics['total_earnings'], 2) }}</h4>
                            <small class="text-muted">{{ __('Total Earnings') }}</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-center">
                            <h4 class="text-warning">{{ number_format($rideStatistics['total_distance'], 1) }} km</h4>
                            <small class="text-muted">{{ __('Distance') }}</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-center">
                            <h4 class="text-secondary">${{ number_format($rideStatistics['average_ride_earnings'], 2) }}
                            </h4>
                            <small class="text-muted">{{ __('Avg Earnings') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent Rides Card --}}
        @if (count($recentRides) > 0)
            <div class="card mt-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">{{ __('Recent Rides') }}</h5>
                    <a href="{{ route('drivers.ride-history', $driver->id) }}" class="btn btn-sm btn-outline-primary">
                        {{ __('View All') }} <i class="fa fa-arrow-right"></i>
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('Ride ID') }}</th>
                                    <th>{{ __('Passenger') }}</th>
                                    <th>{{ __('Route') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Earnings') }}</th>
                                    <th>{{ __('Date') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recentRides as $ride)
                                    <tr>
                                        <td>#{{ $ride['ride_id'] }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if ($ride['user']['user_image_link'])
                                                    <img src="{{ $ride['user']['user_image_link'] }}"
                                                        alt="{{ $ride['user']['user_name'] }}" class="rounded-circle me-2"
                                                        width="30" height="30">
                                                @endif
                                                <div>
                                                    <div class="fw-bold">{{ $ride['user']['user_name'] }}</div>
                                                    <small class="text-muted">{{ $ride['user']['user_phone'] }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <small class="text-success">{{ __('From') }}:
                                                    {{ Str::limit($ride['pickup_address'], 30) }}</small><br>
                                                <small class="text-danger">{{ __('To') }}:
                                                    {{ Str::limit($ride['dropoff_address'], 30) }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            @php
                                                $status = ucfirst(strtolower($ride['status'])); // normalize value

                                                $statusClass = match ($status) {
                                                    'Completed', 'Finshed' => 'success',
                                                    'Cancelled', 'Rejected' => 'danger',
                                                    'In_progress', 'Waiting_user' => 'warning',
                                                    'Accepted' => 'info',
                                                    default => 'secondary',
                                                };
                                            @endphp

                                            <span class="badge bg-{{ $statusClass }}">
                                                {{ __(ucfirst(str_replace('_', ' ', $ride['status']))) }}
                                            </span>
                                        </td>
                                        <td>${{ number_format($ride['calculated_final_price'], 2) }}</td>
                                        <td>{{ \Carbon\Carbon::parse($ride['created_at'])->format('M d, Y H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
