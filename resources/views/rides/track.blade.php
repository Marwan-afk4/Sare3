@extends('layouts.app')
@php
    $currentPage = 'rides';
@endphp
@section('title', 'Track Ride #' . $ride->id)

@push('styles')
    <style>
        #trackingMap {
            height: 70vh;
            min-height: 400px;
            width: 100%;
            border-radius: 8px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
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
            border-radius: 8px;
            padding: 15px;
            background: var(--bs-body-bg);
            /* يورث لون الخلفية من الثيم */
            color: var(--bs-body-color);
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
            padding-left: 20px;
            margin-bottom: 10px;
        }

        body.dark .info-card {
            background: #1e1e2d;
            /* خلفية غامقة */
            border: 1px solid #333;
            color: #f1f1f1;
        }

        .timeline-item::before {
            content: "";
            position: absolute;
            top: 5px;
            left: 0;
            width: 10px;
            height: 10px;
            background: currentColor;
            border-radius: 50%;
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
                                    {{ __('Ride #') }}{{ $ride->id }}
                                </h4>
                                <p class="mb-0">
                                    @if ($ride->status->value === 'in_progress')
                                        <i class="fa fa-broadcast-tower text-success"></i> {{ __('Real-time WebSocket Tracking Active') }}
                                    @elseif (in_array($ride->status->value, ['accepted', 'waiting_user']))
                                        <i class="fa fa-location-arrow text-info"></i> {{ __('Live Tracking Active') }}
                                    @elseif(in_array($ride->status->value, ['completed', 'finshed']))
                                        <i class="fa fa-check-circle text-success"></i> {{ __('Trip Completed') }}
                                    @else
                                        <i class="fa fa-clock text-warning"></i> {{ $ride->status->label() }}
                                    @endif
                                </p>
                            </div>
                            <div class="text-end">
                                <div class="badge bg-light text-dark fs-6 me-2">
                                    {!! $ride->status->badge() !!}
                                </div>
                                @if (in_array($ride->status->value, ['in_progress', 'accepted', 'waiting_user']))
                                    <div id="connection-status" class="badge bg-secondary">
                                        <i class="fa fa-circle-notch fa-spin"></i> {{ __('Connecting...') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div id="trackingMap">
                            <div class="d-flex justify-content-center align-items-center h-100">
                                <div class="text-center">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">{{ __('Loading map...') }}</span>
                                    </div>
                                    <p class="mt-2 text-muted">{{ __('Loading map...') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Info Column -->
            <div class="col-lg-4">
                <!-- Trip Details -->
                <div class="info-card">
                    <h5><i class="fa fa-route text-primary"></i> {{ __('Trip Details') }}</h5>
                    <div class="row">
                        <div class="col-12 mb-2">
                            <p class="text-muted">{{ __('From') }}</p>
                            <div class="fw-bold">{{ $ride->pickup_address ?? __('Pickup Location') }}</div>
                        </div>
                        <div class="col-12 mb-2">
                            <p class="text-muted">{{ __('To') }}</p>
                            @if($ride->dropoff_address && $ride->dropoff_lat && $ride->dropoff_lng)
                                <div class="fw-bold">{{ $ride->dropoff_address }}</div>
                            @else
                                <div class="text-warning fw-bold">
                                    <i class="fa fa-exclamation-triangle"></i> {{ __('No drop-off location selected') }}
                                </div>
                                <small class="text-muted">{{ __('The passenger has not selected a destination yet.') }}</small>
                            @endif
                        </div>
                    </div>

                    <hr>

                    <div class="row text-center">
                        <div class="col-4">
                            <div class="text-muted small">{{ __('Distance') }}</div>
                            <div class="fw-bold">{{ $ride->total_distance_in_km ?? ($ride->estimated_km ?? '-') }}
                                {{ __('km') }}</div>
                        </div>
                        <div class="col-4">
                            <div class="text-muted small">{{ __('Duration') }}</div>
                            <div class="fw-bold">{{ $ride->time_taken ?? ($ride->estimated_time ?? '-') }}
                                {{ __('min') }}</div>
                        </div>
                        <div class="col-4">
                            <div class="text-muted small">{{ __('Price') }}</div>
                            <div class="fw-bold">
                                ${{ $ride->calculated_final_price ?? ($ride->calculated_initial_price ?? '-') }}</div>
                        </div>
                    </div>
                </div>

                {{-- Driver Info --}}
                @if ($ride->driver)
                    <div class="info-card p-3 mb-3 border-start border-success">
                        <h5 class="d-flex align-items-center mb-2">
                            <i class="fa fa-user text-success me-2"></i> {{ __('Driver') }}
                        </h5>
                        <div class="fw-bold">
                            <a href="{{ route('drivers.show', $ride->driver->id) }}"
                                class="text-decoration-none text-success">
                                {{ $ride->driver->name }}
                            </a>
                        </div>
                    </div>
                @endif

                {{-- User Info --}}
                @if ($ride->user)
                    <div class="info-card p-3 mb-3 border-start border-info">
                        <h5 class="d-flex align-items-center mb-2">
                            <i class="fa fa-user text-info me-2"></i> {{ __('Passenger') }}
                        </h5>
                        <div class="fw-bold">
                            <a href="{{ route('users.show', $ride->user->id) }}" class="text-decoration-none text-info">
                                {{ $ride->user->name }}
                            </a>
                        </div>
                    </div>
                @endif

                {{-- Progress Timeline --}}
                <div class="info-card p-3 mb-3 border-start border-warning">
                    <h5 class="d-flex align-items-center mb-3">
                        <i class="fa fa-list text-warning me-2"></i> {{ __('Trip Progress') }}
                    </h5>
                    <ul class="timeline list-unstyled ps-3 mb-0">

                        {{-- Ride Accepted --}}
                        <li class="timeline-item">
                            <i
                                class="fa fa-check-circle
                {{ in_array($ride->status->value, ['accepted', 'waiting_user', 'in_progress', 'completed', 'finshed'])
                    ? 'text-success'
                    : 'text-muted' }}"></i>
                            <strong>{{ __('Ride Accepted') }}</strong>
                            <div class="small text-muted">{{ __('Driver accepted ride request') }}</div>
                            @if($ride->accepted_at)
                                <div class="small text-info mt-1">
                                    <i class="fa fa-clock"></i> {{ $ride->accepted_at->format('M d, Y h:i A') }}
                                </div>
                            @endif
                        </li>

                        {{-- Driver En Route --}}
                        <li class="timeline-item">
                            <i
                                class="fa fa-check-circle
                {{ in_array($ride->status->value, ['waiting_user', 'in_progress', 'completed', 'finshed'])
                    ? 'text-success'
                    : ($ride->status->value === 'accepted'
                        ? 'text-warning'
                        : 'text-muted') }}"></i>
                            <strong>{{ __('Driver En Route') }}</strong>
                            <div class="small text-muted">{{ __('Driver is heading to pickup location') }}</div>
                            @if($ride->accepted_at)
                                <div class="small text-info mt-1">
                                    <i class="fa fa-clock"></i> {{ $ride->accepted_at->format('M d, Y h:i A') }}
                                </div>
                            @endif
                        </li>

                        {{-- Driver Arrived --}}
                        <li class="timeline-item">
                            <i
                                class="fa fa-check-circle
                {{ in_array($ride->status->value, ['in_progress', 'completed', 'finshed'])
                    ? 'text-success'
                    : ($ride->status->value === 'waiting_user'
                        ? 'text-warning'
                        : 'text-muted') }}"></i>
                            <strong>{{ __('Driver Arrived') }}</strong>
                            <div class="small text-muted">{{ __('Driver has arrived at pickup location') }}</div>
                            @if($ride->arrived_at)
                                <div class="small text-info mt-1">
                                    <i class="fa fa-clock"></i> {{ $ride->arrived_at->format('M d, Y h:i A') }}
                                </div>
                            @endif
                        </li>

                        {{-- Trip Started --}}
                        <li class="timeline-item">
                            <i
                                class="fa fa-check-circle
                {{ in_array($ride->status->value, ['completed', 'finshed'])
                    ? 'text-success'
                    : ($ride->status->value === 'in_progress'
                        ? 'text-warning'
                        : 'text-muted') }}"></i>
                            <strong>{{ __('Trip Started') }}</strong>
                            <div class="small text-muted">{{ __('Trip is in progress') }}</div>
                            @if($ride->trip_started_at)
                                <div class="small text-info mt-1">
                                    <i class="fa fa-clock"></i> {{ $ride->trip_started_at->format('M d, Y h:i A') }}
                                </div>
                            @endif
                        </li>

                        {{-- Trip Completed --}}
                        <li class="timeline-item">
                            <i
                                class="fa fa-check-circle
                {{ in_array($ride->status->value, ['completed', 'finshed']) ? 'text-success' : 'text-muted' }}"></i>
                            <strong>{{ __('Trip Completed') }}</strong>
                            <div class="small text-muted">{{ __('You have reached your destination') }}</div>
                            @if($ride->completed_at)
                                <div class="small text-info mt-1">
                                    <i class="fa fa-clock"></i> {{ $ride->completed_at->format('M d, Y h:i A') }}
                                </div>
                            @endif
                        </li>

                    </ul>
                </div>




                <!-- Actions -->
                <div class="info-card">
                    <div class="d-grid gap-2">
                        <a href="{{ route('rides.show', $ride) }}" class="btn btn-outline-primary">
                            <i class="fa fa-info-circle"></i> {{ __('View Details') }}
                        </a>
                        <a href="{{ route('rides.index') }}" class="btn btn-outline-secondary">
                            <i class="fa fa-arrow-left"></i> {{ __('Back to Rides') }}
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
        <!-- Laravel Echo + Pusher (Reverb) — loaded first -->
        <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/pusher-js@8.4.0/dist/web/pusher.min.js"></script>
        <script>
            (function () {
                try {
                    const wsPort  = window.location.port ? parseInt(window.location.port) : (window.location.protocol === 'https:' ? 443 : 80);
                    const useTLS  = window.location.protocol === 'https:';
                    window.Echo = new Echo({
                        broadcaster:       'reverb',
                        key:               '{{ config("broadcasting.connections.reverb.key") }}',
                        wsHost:            window.location.hostname,
                        wsPort:            wsPort,
                        wssPort:           wsPort,
                        forceTLS:          useTLS,
                        enabledTransports: ['ws', 'wss'],
                    });
                    console.log('📡 Laravel Echo (Reverb) initialized for ride tracking');
                } catch (e) {
                    console.warn('Could not initialize Laravel Echo:', e);
                }
            })();
        </script>

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
                driverAcceptLocation: @json($ride->driver_accept_lat && $ride->driver_accept_lng ? ['lat' => (float) $ride->driver_accept_lat, 'lng' => (float) $ride->driver_accept_lng, 'recorded_at' => optional($ride->accepted_at)->toIso8601String()] : null),
                driverArrivedLocation: @json($ride->driver_arrived_lat && $ride->driver_arrived_lng ? ['lat' => (float) $ride->driver_arrived_lat, 'lng' => (float) $ride->driver_arrived_lng, 'recorded_at' => optional($ride->arrived_at)->toIso8601String()] : null)
            };

            let rideTracker;

            function initMap() {
                if (!rideData.pickup.lat || !rideData.pickup.lng) {
                    console.log('No pickup coordinates available');
                    document.getElementById('trackingMap').innerHTML =
                        '<div class="alert alert-warning m-3">No location data available for this ride.</div>';
                    return;
                }

                // Initialize the ride tracker (it will handle no drop-off location internally)
                rideTracker = new SimpleRideTracker(rideData, 'trackingMap');
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
        @if (config('services.google_maps.api_key') && config('services.google_maps.api_key') !== 'your_actual_api_key_here')
            <script async defer
                src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key') }}&callback=initMap&libraries=marker">
            </script>
        @else
            <script>
                function initMap() {
                    document.getElementById('trackingMap').innerHTML =
                        '<div class="alert alert-warning m-3"><strong>Configuration Required:</strong> Please set your Google Maps API key in the .env file.<br><small>Add: GOOGLE_MAPS_API_KEY=your_actual_api_key</small></div>';
                }
                window.addEventListener('load', initMap);
            </script>
        @endif
    @endpush

@endsection
