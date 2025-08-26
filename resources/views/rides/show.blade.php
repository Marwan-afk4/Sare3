@extends('layouts.app')
@php
    $currentPage = 'rides';
@endphp
@section('title', $ride->name)

@push('styles')
<style>
    #rideMap {
        height: 400px;
        min-height: 400px;
        width: 100%;
        border-radius: 8px;
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
    }
    .map-container {
        margin-bottom: 20px;
    }
    .tracking-status {
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 15px;
    }
    .tracking-live {
        background-color: #e3f2fd;
        border-left: 4px solid #2196f3;
    }
    .tracking-completed {
        background-color: #e8f5e8;
        border-left: 4px solid #4caf50;
    }
</style>
@endpush

@section('content')
    <div class="container-fluid">
        <h1>{{ __('Ride') }} #{{ $ride->id }}</h1>
        <div class="mb-3">
            <a href="{{ route('rides.index') }}" class="btn btn-secondary btn-sm me-1"> <i class="fa fa-arrow-left"></i>
                {{ __('Back to') }} {{ __('Rides') }}</a>
            @if($ride->pickup_lat && $ride->pickup_lng)
                <a href="{{ route('rides.track', $ride) }}" class="btn btn-success btn-sm me-1">
                    @if(in_array($ride->status->value, ['in_progress', 'accepted', 'waiting_user']))
                        <i class="fa fa-location-arrow"></i> {{ __('Live Tracking') }}
                    @else
                        <i class="fa fa-route"></i> {{ __('View Route') }}
                    @endif
                </a>
            @endif
            {{-- <a href='{{ route('rides.edit', $ride) }}' class="btn btn-warning btn-sm me-1">{{ __('Edit') }} <i class="fa fa-edit"></i></a> --}}
        </div>
        <!-- Tracking Status -->
        @if(in_array($ride->status->value, ['in_progress', 'accepted', 'waiting_user']))
            <div class="tracking-status tracking-live">
                <h5><i class="fa fa-location-arrow"></i> {{ __('Live Tracking Active') }}</h5>
                <p>{{ __('Driver location is being tracked in real-time') }}</p>
            </div>
        @elseif(in_array($ride->status->value, ['completed', 'finshed']))
            <div class="tracking-status tracking-completed">
                <h5><i class="fa fa-route"></i> {{ __('Trip Route') }}</h5>
                <p>{{ __('Showing the completed trip route') }}</p>
            </div>
        @endif

        <!-- Map Container -->
        @if($ride->pickup_lat && $ride->pickup_lng)
            <div class="map-container">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            @if(in_array($ride->status->value, ['in_progress', 'accepted', 'waiting_user']))
                                <i class="fa fa-location-arrow text-primary"></i> {{ __('Live Ride Tracking') }}
                            @else
                                <i class="fa fa-route text-success"></i> {{ __('Trip Route') }}
                            @endif
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div id="rideMap"></div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Ride Details -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fa fa-info-circle"></i> {{ __('Ride Details') }}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <strong>{{ __('Ride ID') }}:</strong> #{{ $ride->id }}
                            </li>
                            <li class="list-group-item">
                                <strong>{{ __('User') }}:</strong> {{ $ride->user?->name ?? '-' }}
                            </li>
                            <li class="list-group-item">
                                <strong>{{ __('Driver') }}:</strong> {{ $ride->driver?->name ?? '-' }}
                            </li>
                            <li class="list-group-item">
                                <strong>{{ __('Car Category') }}:</strong> {{ $ride->carCategory?->name ?? '-' }}
                            </li>
                            <li class="list-group-item">
                                <strong>{{ __('Status') }}:</strong>
                                {!! $ride->status->badge() !!}
                            </li>
                            <li class="list-group-item">
                                <strong>{{ __('Created At') }}:</strong> {{ $ride->created_at->diffForHumans() ?? '-' }}
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <strong>{{ __('Pickup Address') }}:</strong> 
                                <small class="text-muted d-block">{{ $ride->pickup_address ?? '-' }}</small>
                            </li>
                            <li class="list-group-item">
                                <strong>{{ __('Dropoff Address') }}:</strong> 
                                <small class="text-muted d-block">{{ $ride->dropoff_address ?? '-' }}</small>
                            </li>
                            <li class="list-group-item">
                                <strong>{{ __('Estimated Distance') }}:</strong> {{ $ride->estimated_km ?? '-' }} km
                            </li>
                            <li class="list-group-item">
                                <strong>{{ __('Actual Distance') }}:</strong> {{ $ride->total_distance_in_km ?? '-' }} km
                            </li>
                            <li class="list-group-item">
                                <strong>{{ __('Estimated Time') }}:</strong> {{ $ride->estimated_time ?? '-' }} min
                            </li>
                            <li class="list-group-item">
                                <strong>{{ __('Actual Time') }}:</strong> {{ $ride->time_taken ?? '-' }} min
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pricing Details -->
        @if($ride->calculated_initial_price || $ride->calculated_final_price)
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fa fa-dollar-sign"></i> {{ __('Pricing Details') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>{{ __('Initial Price') }}:</strong> ${{ $ride->calculated_initial_price ?? '0.00' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>{{ __('Final Price') }}:</strong> ${{ $ride->calculated_final_price ?? '0.00' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

@push('scripts')
@include('rides.tracking-scripts')

<script>
// Ride data from Laravel
const rideData = {
    id: {{ $ride->id }},
    status: '{{ $ride->status->value }}',
    pickup: {
        lat: {{ $ride->pickup_lat ?? 'null' }},
        lng: {{ $ride->pickup_lng ?? 'null' }},
        address: '{{ addslashes($ride->pickup_address ?? '') }}'
    },
    dropoff: {
        lat: {{ $ride->dropoff_lat ?? 'null' }},
        lng: {{ $ride->dropoff_lng ?? 'null' }},
        address: '{{ addslashes($ride->dropoff_address ?? '') }}'
    },
    routePoints: @json($ride->route_points ?? []),
    driverId: {{ $ride->driver_id ?? 'null' }},
    firebaseRideId: '{{ $ride->firebase_ride_id ?? '' }}'
};

let rideTracker;

function initMap() {
    if (!rideData.pickup.lat || !rideData.pickup.lng) {
        console.log('No pickup coordinates available');
        document.getElementById('rideMap').innerHTML = '<div class="alert alert-warning">No location data available for this ride.</div>';
        return;
    }

    // Initialize the ride tracker
    rideTracker = new SimpleRideTracker(rideData, 'rideMap');
    rideTracker.init();
}

// Cleanup function
function cleanup() {
    if (rideTracker) {
        rideTracker.cleanup();
    }
}

// Auto-refresh ride data for active rides
if (['in_progress', 'accepted', 'waiting_user'].includes(rideData.status)) {
    setInterval(() => {
        if (rideTracker) {
            rideTracker.refreshRideData();
        }
    }, 30000); // Refresh every 30 seconds
}

// Initialize map when page loads
window.addEventListener('load', initMap);

// Cleanup when page unloads
window.addEventListener('beforeunload', cleanup);
</script>

<!-- Google Maps API -->
@if(config('services.google_maps.api_key') && config('services.google_maps.api_key') !== 'your_actual_api_key_here')
<script async defer 
    src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key') }}&callback=initMap&libraries=marker">
</script>
@else
<script>
    function initMap() {
        document.getElementById('rideMap').innerHTML = '<div class="alert alert-warning m-3"><strong>Configuration Required:</strong> Please set your Google Maps API key in the .env file.<br><small>Add: GOOGLE_MAPS_API_KEY=your_actual_api_key</small></div>';
    }
    window.addEventListener('load', initMap);
</script>
@endif

<!-- Firebase SDK (if you want real-time updates) -->
<script src="https://www.gstatic.com/firebasejs/9.0.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.0.0/firebase-database-compat.js"></script>
<script>
// Initialize Firebase for real-time tracking
const firebaseConfig = {
    databaseURL: 'https://sarea-adce3-default-rtdb.firebaseio.com'
};

if (typeof firebase !== 'undefined') {
    firebase.initializeApp(firebaseConfig);
}
</script>
@endpush

@endsection
