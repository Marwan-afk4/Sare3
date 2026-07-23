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
                                @if ($driver->activity)
                                    {!! $driver->activity->badge() !!}
                                @else
                                    -
                                @endif
                            </li>
                            <li class="list-group-item"><strong>{{ __('Wallet') }}:</strong>
                                @if ($driver->wallet < 0)
                                    <span style="color: red;">{{ $driver->wallet }} 🔴</span>
                                @else
                                    {{ $driver->wallet ?? '-' }}
                                @endif
                            </li>
                            <li class="list-group-item"><strong>{{ __('Zone') }}:</strong>
                                @if ($driver->zone)
                                    <span class="badge bg-info">{{ $driver->zone->name }}</span>
                                @else
                                    <span class="text-muted">{{ __('We do not know yet') }}</span>
                                @endif
                            </li>
                            <li class="list-group-item"><strong>{{ __('City') }}:</strong>
                                @if ($driver->city)
                                    <span class="badge bg-primary">{{ $driver->city->name }}</span>
                                @else
                                    <span class="text-muted">{{ __('We do not know yet') }}</span>
                                @endif
                            </li>
                            <li class="list-group-item"><strong>{{ __('Driver Rating') }}:</strong>
                                @if ($driverRating)
                                    <span class="badge bg-warning">{{ number_format($driverRating, 1) }} ⭐</span>
                                @else
                                    <span class="text-muted">{{ __('No ratings yet') }}</span>
                                @endif
                            </li>
                            <li class="list-group-item"><strong>{{ __('Availability') }}:</strong>
                                <span id="driver-availability-badge">
                                    @if ($isAvailable)
                                        <span class="badge bg-success">{{ __('الحالة: متصل ومتاح') }} 🟢</span>
                                    @else
                                        <span class="badge bg-danger">{{ __('Offline') }} 🔴</span>
                                    @endif
                                </span>
                            </li>
                            <li class="list-group-item"><strong>{{ __('Created At') }}:</strong>
                                {{ $driver->created_at?->diffForHumans() }}</li>
                            <li class="list-group-item"><strong>{{ __('Updated At') }}:</strong>
                                {{ $driver->updated_at?->diffForHumans() }}</li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Admin-only notes --}}
            <div class="card-body border-top">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">
                        <i class="fa fa-sticky-note me-2"></i>{{ __('Admin Notes') }}
                    </h5>
                    <span class="badge bg-secondary">{{ __('Visible to admins only') }}</span>
                </div>

                @if (session('success'))
                    <div class="alert alert-success py-2">{{ session('success') }}</div>
                @endif

                <form method="POST" action="{{ route('drivers.notes.update', $driver) }}">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <textarea
                            name="admin_notes"
                            id="admin_notes"
                            class="form-control @error('admin_notes') is-invalid @enderror"
                            rows="4"
                            placeholder="{{ __('Add internal notes about this driver...') }}"
                        >{{ old('admin_notes', $driver->admin_notes) }}</textarea>
                        @error('admin_notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">{{ __('These notes are never shown to the driver or in the mobile app.') }}</small>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fa fa-save me-1"></i>{{ __('Save Notes') }}
                    </button>
                </form>
            </div>

            {{-- Driver Location Map --}}
            @if ($driverLocation)
                <div class="card-body border-top">
                    <h5 class="mb-3">{{ __('Last Known Location') }}</h5>
                    <div id="driver-location-map" style="height: 400px; border-radius: 8px;"></div>
                    <div class="mt-2 text-muted small">
                        <i class="fa fa-info-circle"></i> {{ $isAvailable ? __('Location updates in real-time via WebSocket') : __('Location updates in real-time when driver is online.') }}
                    </div>
                </div>
            @endif
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
                <form method="POST" action="{{ route('drivers.destroy', $driver) }}" class="d-inline" onsubmit="return confirm('{{ __('Are you sure you want to delete this driver and all their data (documents, cars, etc.)? This cannot be undone.') }}');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm">{{ __('Delete Driver') }} <i class="fa fa-trash"></i></button>
                </form>
            </div>
        </div>

        {{-- Driver cars --}}
        <div class="card mt-4">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h5 class="card-title mb-0">{{ __('Cars') }}</h5>
                <div class="d-flex flex-wrap gap-1">
                    <a href="{{ route('drivers.cars', $driver->id) }}" class="btn btn-sm btn-outline-primary">
                        {{ __('View all') }} <i class="fa fa-car"></i>
                    </a>
                    <a href="{{ route('driver-cars.create') }}?driver_id={{ $driver->id }}" class="btn btn-sm btn-primary">
                        {{ __('Add New Car') }} <i class="fa fa-plus"></i>
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if ($driver->driverCars->isEmpty())
                    <p class="text-muted mb-0">{{ __('No cars registered for this driver.') }}</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('Car Number') }}</th>
                                    <th>{{ __('Categories') }}</th>
                                    <th>{{ __('Model') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th class="text-end">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($driver->driverCars as $car)
                                    <tr>
                                        <td><span class="badge bg-primary">{{ $car->car_number }}</span></td>
                                        <td>
                                            @forelse($car->carCategories as $category)
                                                <span class="badge bg-secondary mb-1">{{ $category->name }}</span>
                                            @empty
                                                {{ $car->carCategory->name ?? '—' }}
                                            @endforelse
                                        </td>
                                        <td>{{ $car->carModel->name ?? '—' }}</td>
                                        <td>{{ $car->carType->type_name ?? '—' }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('driver-cars.show', $car) }}"
                                                class="btn btn-subtle-primary btn-sm me-1">{{ __('Details') }}
                                                <i class="fa fa-eye"></i></a>
                                            <a href="{{ route('driver-cars.edit', $car) }}"
                                                class="btn btn-subtle-warning btn-sm me-1">{{ __('Edit') }}
                                                <i class="fa fa-edit"></i></a>
                                            <form method="POST" action="{{ route('driver-cars.destroy', $car) }}"
                                                class="d-inline"
                                                onsubmit="return confirm('{{ __('Are you sure you want to delete this car?') }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-subtle-danger btn-sm">
                                                    {{ __('Delete') }} <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
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

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        .driver-marker {
            background-color: #4CAF50;
            border: 3px solid white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
        }
        .unavailable-driver-marker {
            background-color: #f44336;
            border: 3px solid white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
        }
    </style>
@endpush
@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <!-- Laravel Echo & Pusher JS for real-time WebSocket updates -->
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/pusher-js@8.4.0/dist/web/pusher.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if ($driverLocation)
                // Initialize the map
                const initialLat = {{ $driverLocation['latitude'] }};
                const initialLng = {{ $driverLocation['longitude'] }};

                const map = L.map('driver-location-map').setView([initialLat, initialLng], 15);

                // Add tile layer (OpenStreetMap)
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors',
                    maxZoom: 19,
                }).addTo(map);

                // Custom driver icon (Green for online, Red for offline)
                const isOnline = {{ $isAvailable ? 'true' : 'false' }};
                const color = isOnline ? '#2ecc71' : '#e74c3c';
                const initialBearing = {{ $driverLocation['bearing'] !== null ? $driverLocation['bearing'] : 'null' }};
                const rotateStyle = initialBearing !== null ? `transform: rotate(${initialBearing}deg);` : '';
                const carSvg = `
                    <svg width="36" height="36" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                        <rect x="25" y="10" width="50" height="80" rx="15" fill="${color}" stroke="#fff" stroke-width="4"/>
                        <rect x="30" y="25" width="40" height="25" rx="5" fill="#333" opacity="0.8"/>
                        <rect x="30" y="60" width="40" height="15" rx="3" fill="#333" opacity="0.8"/>
                        <rect x="20" y="20" width="5" height="15" rx="2" fill="#fff" opacity="0.5"/>
                        <rect x="75" y="20" width="5" height="15" rx="2" fill="#fff" opacity="0.5"/>
                        <rect x="20" y="70" width="5" height="10" rx="2" fill="red" opacity="0.8"/>
                        <rect x="75" y="70" width="5" height="10" rx="2" fill="red" opacity="0.8"/>
                    </svg>
                `;

                const driverIcon = L.divIcon({
                    html: `<div class="car-icon-wrapper" style="width:36px;height:36px;transition: transform 0.2s;${rotateStyle}">${carSvg}</div>`,
                    iconSize: [36, 36],
                    iconAnchor: [18, 18],
                    className: ''
                });

                // Add driver marker
                let driverMarker = L.marker([initialLat, initialLng], {
                    icon: driverIcon,
                    title: '{{ $driver->name }}'
                }).addTo(map);

                // Add popup to marker
                driverMarker.bindPopup(`
                    <div style="text-align: center;">
                        <strong>{{ $driver->name }}</strong><br>
                        @if ($isAvailable)
                            <span class="badge bg-success">{{ __('Online') }}</span><br>
                            <small>{{ __('Last updated') }}: <span id="last-updated">{{ __('Just now') }}</span></small>
                        @else
                            <span class="badge bg-danger">{{ __('Offline') }}</span><br>
                            <small>{{ __('Last known location') }}</small>
                        @endif
                    </div>
                `).openPopup();

                @if ($isAvailable)
                    // Initialize Laravel Echo via WebSocket
                    const _wsPort = window.location.port ? parseInt(window.location.port) : (window.location.protocol === 'https:' ? 443 : 80);
                    const _useTLS = window.location.protocol === 'https:';
                    const echoConfig = {
                        broadcaster:       'reverb',
                        key:               '{{ config("broadcasting.connections.reverb.key") }}',
                        wsHost:            window.location.hostname,
                        wsPort:            _wsPort,
                        wssPort:           _wsPort,
                        forceTLS:          _useTLS,
                        enabledTransports: ['ws', 'wss'],
                    };

                    console.log('📡 Initializing Echo with config:', echoConfig);
                    const echo = new Echo(echoConfig);

                    // Function to format timestamp
                    function formatLastUpdated(timestamp) {
                        if (!timestamp) return '{{ __('Just now') }}';
                        const date = new Date(timestamp);
                        const now = new Date();
                        const diffSeconds = Math.floor((now - date) / 1000);

                        if (diffSeconds < 10) return '{{ __('Just now') }}';
                        if (diffSeconds < 60) return diffSeconds + ' {{ __('seconds ago') }}';
                        if (diffSeconds < 3600) return Math.floor(diffSeconds / 60) + ' {{ __('minutes ago') }}';
                        return date.toLocaleTimeString();
                    }

                    // Function to update marker position
                    function updateMarkerPosition(lat, lng, timestamp, bearing = null) {
                        const newLat = parseFloat(lat);
                        const newLng = parseFloat(lng);

                        // Update marker position with smooth animation
                        driverMarker.setLatLng([newLat, newLng]);

                        // Rotate the car icon wrapper div if bearing is available
                        if (bearing !== null && bearing !== undefined) {
                            const wrapper = driverMarker.getElement()?.querySelector('.car-icon-wrapper');
                            if (wrapper) {
                                wrapper.style.transform = `rotate(${bearing}deg)`;
                            }
                        }

                        // Smoothly pan map to new location (only if significant movement)
                        const currentCenter = map.getCenter();
                        const distance = map.distance(currentCenter, [newLat, newLng]);
                        if (distance > 100) { // Only pan if moved more than 100 meters
                            map.panTo([newLat, newLng], {
                                animate: true,
                                duration: 1.0
                            });
                        }

                        // Update popup content
                        const lastUpdated = formatLastUpdated(timestamp);
                        driverMarker.getPopup().setContent(`
                            <div style="text-align: center;">
                                <strong>{{ $driver->name }}</strong><br>
                                <span class="badge bg-success">{{ __('Online') }}</span><br>
                                <small>{{ __('Last updated') }}: <span id="last-updated">${lastUpdated}</span></small>
                            </div>
                        `);

                        // Update availability badge
                        document.getElementById('driver-availability-badge').innerHTML =
                            '<span class="badge bg-success">{{ __('الحالة: متصل ومتاح') }} 🟢</span>';
                    }

                    // Function to handle driver going offline
                    function handleDriverOffline() {
                        document.getElementById('driver-availability-badge').innerHTML =
                            '<span class="badge bg-danger">{{ __('Offline') }} 🔴</span>';
                    }

                    // Listen to driver-location public channel
                    echo.channel('driver-location')
                        .listen('.driver.location.updated', (e) => {
                            if (String(e.driver_id) === '{{ $driver->id }}') {
                                console.log('📡 Real-time location update received:', e);
                                updateMarkerPosition(e.latitude, e.longitude, e.updated_at, e.bearing);
                            }
                        });

                    // Update UI status when connection state changes
                    echo.connector.pusher.connection.bind('connected', () => {
                        console.log('📡 WebSocket Connected');
                    });
                    echo.connector.pusher.connection.bind('disconnected', () => {
                        console.log('📡 WebSocket Disconnected');
                        handleDriverOffline();
                    });
                @endif
            @endif
        });
    </script>
@endpush
