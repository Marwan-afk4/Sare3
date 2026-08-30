@extends('layouts.app')
@php
    $currentPage = 'users';
@endphp
@section('title', $user->name)
@section('content')
    <div class="container-fluid">
        <h1>{{ $user->name }}</h1>
        <div class="mb-3">
            <a href="{{ route('users.index') }}" class="btn btn-secondary btn-sm me-1">
                <i class="fa fa-arrow-right"></i> {{ __('Back to') }} {{ __('Users') }}
            </a>
            {{-- <a href='{{ route('users.edit', $user) }}' class="btn btn-warning btn-sm me-1">
                {{ __('Edit') }} <i class="fa fa-edit"></i>
            </a> --}}
        </div>

        <div class="card">
            <div class="card-body">
                <div class="row">
                    {{-- Profile Image --}}
                    <div class="col-md-2 text-center">
                        @if ($user->image)
                            <img src="{{ asset('storage/' . $user->image) }}" alt="{{ $user->name }}"
                                class="img-thumbnail mb-3" style="max-width: 100%;">
                        @else
                            <img src="https://th.bing.com/th/id/OIP.hGSCbXlcOjL_9mmzerqAbQHaHa?rs=1&pid=ImgDetMain"
                                alt="No Image" class="img-thumbnail mb-3">
                        @endif
                    </div>

                    {{-- Info --}}
                    <div class="col-md-9">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item"><strong>{{ __('Id') }}:</strong> {{ $user->id }}</li>
                            <li class="list-group-item"><strong>{{ __('Name') }}:</strong> {{ $user->name }}</li>
                            <li class="list-group-item"><strong>{{ __('Email') }}:</strong> {{ $user->email }}</li>
                            <li class="list-group-item"><strong>{{ __('Phone') }}:</strong> {{ $user->phone }}</li>
                            <li class="list-group-item"><strong>{{ __('Activity') }}:</strong>
                                <td>{!! $user->activity->badge() !!}</td>
                            </li>
                            <li class="list-group-item"><strong>{{ __('Wallet') }}:</strong> {{ $user->wallet }}</li>
                            <li class="list-group-item">
                                <strong>{{ __('Signup gift') }}:</strong>
                                @if($user->hasReceivedSignupGift())
                                    <span class="badge bg-success">
                                        {{ __('Received') }} · {{ number_format((float) $user->signup_gift_amount, 2) }}
                                    </span>
                                    <small class="text-muted ms-2">
                                        {{ optional($user->signup_gift_received_at)->diffForHumans() }}
                                    </small>
                                @else
                                    <span class="badge bg-warning text-dark">{{ __('Not gifted yet') }}</span>
                                @endif
                            </li>
                            <li class="list-group-item"><strong>{{ __('User Rating') }}:</strong>
                                @if ($userRating)
                                    <span class="badge bg-warning">{{ number_format($userRating, 1) }} ⭐</span>
                                @else
                                    <span class="text-muted">{{ __('No ratings yet') }}</span>
                                @endif
                            </li>
                            <li class="list-group-item"><strong>{{ __('Created At') }}:</strong>
                                {{ $user->created_at?->diffForHumans() }}</li>
                            <li class="list-group-item"><strong>{{ __('Updated At') }}:</strong>
                                {{ $user->updated_at?->diffForHumans() }}</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <a href='{{ route('users.edit', $user) }}' class="btn btn-subtle-warning btn-sm me-1">
                    {{ __('Edit') }} <i class="fa fa-edit"></i>
                </a>
                <a href="{{ route('users.ride-history', $user->id) }}" class="btn btn-subtle-info btn-sm me-1">
                    {{ __('Ride History') }} <i class="fa fa-history"></i>
                </a>
                @if(! $user->hasReceivedSignupGift())
                    <button type="button" class="btn btn-subtle-success btn-sm" data-bs-toggle="modal" data-bs-target="#grantSignupGiftModal">
                        {{ __('Give signup gift') }} <i class="fa fa-gift"></i>
                    </button>
                @endif
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
                            <h4 class="text-info">${{ number_format($rideStatistics['total_spent'], 2) }}</h4>
                            <small class="text-muted">{{ __('Total Spent') }}</small>
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
                            <h4 class="text-secondary">${{ number_format($rideStatistics['average_ride_cost'], 2) }}</h4>
                            <small class="text-muted">{{ __('Avg Cost') }}</small>
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
                    <a href="{{ route('users.ride-history', $user->id) }}" class="btn btn-sm btn-outline-primary">
                        {{ __('View All') }} <i class="fa fa-arrow-right"></i>
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('Ride ID') }}</th>
                                    <th>{{ __('Driver') }}</th>
                                    <th>{{ __('Route') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Price') }}</th>
                                    <th>{{ __('Date') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recentRides as $ride)
                                    <tr>
                                        <td>#{{ $ride['ride_id'] }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if ($ride['driver']['driver_image_link'])
                                                    <img src="{{ $ride['driver']['driver_image_link'] }}"
                                                        alt="{{ $ride['driver']['driver_name'] }}"
                                                        class="rounded-circle me-2" width="30" height="30">
                                                @endif
                                                <div>
                                                    <div class="fw-bold">{{ $ride['driver']['driver_name'] }}</div>
                                                    <small class="text-muted">{{ $ride['driver']['driver_phone'] }}</small>
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

    @if(! $user->hasReceivedSignupGift())
        <div class="modal fade" id="grantSignupGiftModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <form method="POST" action="{{ route('signup-gifts.grant', $user) }}">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="fas fa-gift me-2"></i>{{ __('Give signup gift') }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p>{{ __('This will add the gift to the wallet of') }} <strong>{{ $user->name ?: $user->phone }}</strong>.</p>
                            <label class="form-label fw-semibold">{{ __('Amount') }}</label>
                            <div class="input-group">
                                <input type="number" name="amount" class="form-control" min="0.01" max="999999"
                                    step="0.01" value="{{ $signupGiftAmount }}" required>
                                <span class="input-group-text">{{ __('JOD') }}</span>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-gift me-1"></i>{{ __('Give gift') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection
