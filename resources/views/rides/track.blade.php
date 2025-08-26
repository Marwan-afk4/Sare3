@extends('layouts.app')
@php
    $currentPage = 'rides';
@endphp
@section('title', 'Track Ride #' . $ride->id)

@push('styles')
    <style>
        #trackingMap {
            height: 70vh;
            width: 100%;
            border-radius: 8px;
        }

        .tracking-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px 10px 0 0;
        }

        .status-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 8px;
            animation: pulse 2s infinite;
        }

        .status-live {
            background-color: #4CAF50;
        }

        .status-completed {
            background-color: #2196F3;
        }

        .status-pending {
            background-color: #FF9800;
        }

        @keyframes pulse {
            0% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }

            100% {
                opacity: 1;
            }
        }

        .info-card {
            background: white;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .progress-timeline {
            position: relative;
            padding-left: 30px;
        }

        .progress-timeline::before {
            content: '';
            position: absolute;
            left: 10px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e0e0e0;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 20px;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -25px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #e0e0e0;
        }

        .timeline-item.completed::before {
            background: #4CAF50;
        }

        .timeline-item.active::before {
            background: #2196F3;
            animation: pulse 2s infinite;
        }

        .refresh-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }

        .driver-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .driver-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #2196F3;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="row">
            <!-- Map Column -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="tracking-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="mb-1">
                                    <span
                                        class="status-indicator status-{{ $ride->status->value === 'completed' || $ride->status->value === 'finshed' ? 'completed' : ($ride->status->value === 'in_progress' || $ride->status->value === 'accepted' || $ride->status->value === 'waiting_user' ? 'live' : 'pending') }}"></span>
                                    Ride #{{ $ride->id }}
                                </h4>
                                <p class="mb-0">
                                    @if (in_array($ride->status->value, ['in_progress', 'accepted', 'waiting_user']))
                                        <i class="fa fa-location-arrow"></i> Live Tracking Active
                                    @elseif(in_array($ride->status->value, ['completed', 'finshed']))
                                        <i class="fa fa-check-circle"></i> Trip Completed
                                    @else
                                        <i class="fa fa-clock"></i> {{ $ride->status->label() }}
                                    @endif
                                </p>
                            </div>
                            <div class="text-end">
                                <div class="badge bg-light text-dark fs-6">
                                    {!! $ride->status->badge() !!}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div id="trackingMap"></div>
                    </div>
                </div>
            </div>

            <!-- Info Column -->
            <div class="col-lg-4">
                <!-- Trip Details -->
                <div class="info-card">
                    <h5><i class="fa fa-route text-primary"></i> Trip Details</h5>
                    <div class="row">
                        <div class="col-12 mb-2">
                            <small class="text-muted">From</small>
                            <div class="fw-bold">{{ $ride->pickup_address ?? 'Pickup Location' }}</div>
                        </div>
                        <div class="col-12 mb-2">
                            <small class="text-muted">To</small>
                            <div class="fw-bold">{{ $ride->dropoff_address ?? 'Dropoff Location' }}</div>
                        </div>
                    </div>

                    <hr>

                    <div class="row text-center">
                        <div class="col-4">
                            <div class="text-muted small">Distance</div>
                            <div class="fw-bold">{{ $ride->total_distance_in_km ?? ($ride->estimated_km ?? '-') }} km</div>
                        </div>
                        <div class="col-4">
                            <div class="text-muted small">Duration</div>
                            <div class="fw-bold">{{ $ride->time_taken ?? ($ride->estimated_time ?? '-') }} min</div>
                        </div>
                        <div class="col-4">
                            <div class="text-muted small">Price</div>
                            <div class="fw-bold">
                                ${{ $ride->calculated_final_price ?? ($ride->calculated_initial_price ?? '-') }}</div>
                        </div>
                    </div>
                </div>

                <!-- Driver Info -->
                @if ($ride->driver)
                    <div class="info-card p-3 mb-2" style="border-left: 5px solid #28a745; background: #eafaf0;">
                        <h5><i class="fa fa-user text-success"></i> Driver</h5>
                        <div class="fw-bold">
                            <a href="{{ route('drivers.show', $ride->driver->id) }}" class="text-success text-decoration-none">
                                {{ $ride->driver->name }}
                            </a>
                        </div>
                    </div>
                @endif

                <!-- User Info -->
                @if ($ride->user)
                    <div class="info-card p-3 mb-2" style="border-left: 5px solid #17a2b8; background: #eaf4fa;">
                        <h5><i class="fa fa-user text-info"></i> Passenger</h5>
                        <div class="fw-bold">
                            <a href="{{ route('users.show', $ride->user->id) }}" class="text-info text-decoration-none">
                                {{ $ride->user->name }}
                            </a>
                        </div>
                    </div>
                @endif



            <!-- Trip Progress -->
            <div class="info-card p-3 mb-2" style="border-left: 5px solid #ffc107; background: #fff8e1;">
                <h5><i class="fa fa-list text-warning"></i> Trip Progress</h5>
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <i class="fa fa-check-circle {{ in_array($ride->status->value, ['accepted','waiting_user','in_progress','completed','finshed']) ? 'text-success' : 'text-muted' }}"></i>
                        <strong> Ride Accepted</strong>
                        <div class="small text-muted">Driver accepted your ride request</div>
                    </li>
                    <li class="mb-2">
                        <i class="fa fa-check-circle {{ in_array($ride->status->value, ['waiting_user','in_progress','completed','finshed']) ? 'text-success' : ($ride->status->value === 'accepted' ? 'text-warning' : 'text-muted') }}"></i>
                        <strong> Driver En Route</strong>
                        <div class="small text-muted">Driver is heading to pickup location</div>
                    </li>
                    <li class="mb-2">
                        <i class="fa fa-check-circle {{ in_array($ride->status->value, ['in_progress','completed','finshed']) ? 'text-success' : ($ride->status->value === 'waiting_user' ? 'text-warning' : 'text-muted') }}"></i>
                        <strong> Driver Arrived</strong>
                        <div class="small text-muted">Driver has arrived at pickup location</div>
                    </li>
                    <li class="mb-2">
                        <i class="fa fa-check-circle {{ in_array($ride->status->value, ['completed','finshed']) ? 'text-success' : ($ride->status->value === 'in_progress' ? 'text-warning' : 'text-muted') }}"></i>
                        <strong> Trip Started</strong>
                        <div class="small text-muted">Trip is in progress</div>
                    </li>
                    <li>
                        <i class="fa fa-check-circle {{ in_array($ride->status->value, ['completed','finshed']) ? 'text-success' : 'text-muted' }}"></i>
                        <strong> Trip Completed</strong>
                        <div class="small text-muted">You have reached your destination</div>
                    </li>
                </ul>
            </div>


                <!-- Actions -->
                <div class="info-card">
                    <div class="d-grid gap-2">
                        <a href="{{ route('rides.show', $ride) }}" class="btn btn-outline-primary">
                            <i class="fa fa-info-circle"></i> View Details
                        </a>
                        <a href="{{ route('rides.index') }}" class="btn btn-outline-secondary">
                            <i class="fa fa-arrow-left"></i> Back to Rides
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Refresh Button -->
    @if (in_array($ride->status->value, ['in_progress', 'accepted', 'waiting_user']))
        <button class="btn btn-primary refresh-btn" onclick="refreshTracking()" title="Refresh Tracking">
            <i class="fa fa-sync-alt"></i>
        </button>
    @endif

    @push('scripts')
        @vite('resources/js/ride-tracking.js')

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
                    document.getElementById('trackingMap').innerHTML =
                        '<div class="alert alert-warning m-3">No location data available for this ride.</div>';
                    return;
                }

                // Initialize the ride tracker
                rideTracker = new RideTracker(rideData, 'trackingMap');
                rideTracker.init();
            }

            function refreshTracking() {
                if (rideTracker) {
                    rideTracker.refreshRideData();
                }

                // Add visual feedback
                const btn = document.querySelector('.refresh-btn i');
                btn.classList.add('fa-spin');
                setTimeout(() => {
                    btn.classList.remove('fa-spin');
                }, 1000);
            }

            // Cleanup function
            function cleanup() {
                if (rideTracker) {
                    rideTracker.cleanup();
                }
            }

            // Auto-refresh for active rides
            if (['in_progress', 'accepted', 'waiting_user'].includes(rideData.status)) {
                setInterval(() => {
                    if (rideTracker) {
                        rideTracker.refreshRideData();
                    }
                }, 15000); // Refresh every 15 seconds for tracking page
            }

            // Initialize map when page loads
            window.addEventListener('load', initMap);
            window.addEventListener('beforeunload', cleanup);
        </script>

        <!-- Google Maps API -->
        <script async defer
            src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key', 'YOUR_GOOGLE_MAPS_API_KEY') }}&callback=initMap">
        </script>

        <!-- Firebase SDK -->
        <script src="https://www.gstatic.com/firebasejs/9.0.0/firebase-app-compat.js"></script>
        <script src="https://www.gstatic.com/firebasejs/9.0.0/firebase-database-compat.js"></script>
        <script>
            const firebaseConfig = {
                databaseURL: 'https://sarea-adce3-default-rtdb.firebaseio.com'
            };

            if (typeof firebase !== 'undefined') {
                firebase.initializeApp(firebaseConfig);
            }
        </script>
    @endpush

@endsection
