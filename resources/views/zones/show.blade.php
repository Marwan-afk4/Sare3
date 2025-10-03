@extends('layouts.app')
@php
    $currentPage = 'zones';
@endphp
@section('title', $zone->name)
@section('content')
    <div class="container-fluid">
        <h1>{{ $zone->name }}</h1>
        <div class="mb-3">
            <a href="{{ route('zones.index') }}" class="btn btn-secondary btn-sm me-1"> <i class="fa fa-arrow-right"></i>
                {{ __('Back to') }} {{ __('Zones') }}</a>
            <a href='{{ route('zones.edit', $zone) }}' class="btn btn-warning btn-sm me-1">{{ __('Edit') }} <i
                    class="fa fa-edit"></i></a>
        </div>

        <div class="row">
            <!-- Zone Details -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Zone Details') }}</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>{{ __('ID') }}:</strong>
                                <span>{{ $zone->id }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>{{ __('Name') }}:</strong>
                                <span>{{ $zone->name }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>{{ __('Zone Type') }}:</strong>
                                <span>
                                    @if (is_array($zone->polygon_coordinates))
                                        <span class="badge bg-success">{{ __('Polygon') }}</span>
                                        <small class="text-muted d-block">{{ count($zone->polygon_coordinates) }}
                                            {{ __('points') }}</small>
                                    @else
                                        <span class="badge bg-secondary">{{ __('Rectangle') }}</span>
                                    @endif
                                </span>
                            </li>
                            @if (!$zone->polygon_coordinates)
                                <li class="list-group-item">
                                    <strong>{{ __('Coordinates') }}:</strong>
                                    <div class="mt-2">
                                        <small class="text-muted">{{ __('From') }}: {{ $zone->from_lat }},
                                            {{ $zone->from_lng }}</small><br>
                                        <small class="text-muted">{{ __('To') }}: {{ $zone->to_lat }},
                                            {{ $zone->to_lng }}</small>
                                    </div>
                                </li>
                            @endif
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>{{ __('Created') }}:</strong>
                                <span>{{ $zone->created_at?->diffForHumans() ?? '-' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>{{ __('Updated') }}:</strong>
                                <span>{{ $zone->updated_at?->diffForHumans() ?? '-' }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Zone Map -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">{{ __('Zone Map') }}</h5>
                        {{-- <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-primary"
                                id="fitBounds">{{ __('Fit to Zone') }}</button>
                            <button type="button" class="btn btn-outline-secondary"
                                id="toggleSatellite">{{ __('Satellite') }}</button>
                        </div> --}}
                    </div>
                    <div class="card-body p-0">
                        @if (env('GOOGLE_MAPS_API_KEY'))
                            <div id="zoneMap" style="height: 500px; width: 100%; position: relative;">
                                <div id="mapLoading"
                                    style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 1000;">
                                    <div class="text-center">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">{{ __('Loading...') }}</span>
                                        </div>
                                        <div class="mt-2">{{ __('Loading Map...') }}</div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-warning m-3">
                                <strong>{{ __('Google Maps API Key Required') }}</strong><br>
                                {{ __('Please configure GOOGLE_MAPS_API_KEY in your .env file to view the zone map.') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <br>
        {{-- Car Categories --}}
        <livewire:zone-car-categories :zone="$zone" />
    </div>

    @if (env('GOOGLE_MAPS_API_KEY'))
        @push('scripts')
            <script>
                let map;
                let zonePolygon = null;
                let zoneRectangle = null;

                // Zone data from server
                const zoneData = {
                    id: {{ $zone->id }},
                    name: @json($zone->name),
                    polygon_coordinates: @json($zone->polygon_coordinates ?? []),
                    from_lat: {{ $zone->from_lat ?? 'null' }},
                    from_lng: {{ $zone->from_lng ?? 'null' }},
                    to_lat: {{ $zone->to_lat ?? 'null' }},
                    to_lng: {{ $zone->to_lng ?? 'null' }}
                };

                console.log('Zone data:', zoneData); // Debug log
                console.log('Polygon coordinates type:', typeof zoneData.polygon_coordinates);
                console.log('Polygon coordinates value:', zoneData.polygon_coordinates);

                function initZoneMap() {
                    console.log('Initializing zone map...'); // Debug log

                    try {
                        // Check if Google Maps is loaded
                        if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
                            console.error('Google Maps API not loaded');
                            document.getElementById('zoneMap').innerHTML =
                                '<div class="alert alert-danger m-3">Google Maps failed to load. Please check your API key and internet connection.</div>';
                            return;
                        }

                        // Default center
                        let mapCenter = {
                            lat: 24.7136,
                            lng: 46.6753
                        }; // Riyadh, Saudi Arabia
                        let mapZoom = 11;

                        // Parse polygon coordinates if it's a string
                        let polygonCoords = zoneData.polygon_coordinates;
                        if (typeof polygonCoords === 'string') {
                            try {
                                polygonCoords = JSON.parse(polygonCoords);
                            } catch (e) {
                                console.error('Error parsing polygon coordinates:', e);
                                polygonCoords = [];
                            }
                        }

                        // Calculate center based on zone data
                        if (polygonCoords && Array.isArray(polygonCoords) && polygonCoords.length > 0) {
                            console.log('Loading polygon zone with', polygonCoords.length, 'points');
                            // Center on polygon
                            const bounds = new google.maps.LatLngBounds();
                            polygonCoords.forEach(coord => {
                                bounds.extend(new google.maps.LatLng(coord.lat, coord.lng));
                            });
                            mapCenter = bounds.getCenter().toJSON();
                            mapZoom = 12;
                        } else if (zoneData.from_lat && zoneData.from_lng && zoneData.to_lat && zoneData.to_lng) {
                            console.log('Loading rectangle zone');
                            // Center on rectangle
                            mapCenter = {
                                lat: (zoneData.from_lat + zoneData.to_lat) / 2,
                                lng: (zoneData.from_lng + zoneData.to_lng) / 2
                            };
                            mapZoom = 12;
                        }

                        console.log('Map center:', mapCenter);

                        // Initialize map
                        map = new google.maps.Map(document.getElementById('zoneMap'), {
                            zoom: mapZoom,
                            center: mapCenter,
                            mapTypeId: 'roadmap',
                            streetViewControl: false,
                            fullscreenControl: true,
                            mapTypeControl: true,
                            zoomControl: true
                        });

                        console.log('Map initialized successfully');

                        // Hide loading indicator
                        const loadingElement = document.getElementById('mapLoading');
                        if (loadingElement) {
                            loadingElement.style.display = 'none';
                        }

                        // Display zone based on type
                        if (polygonCoords && Array.isArray(polygonCoords) && polygonCoords.length > 0) {
                            displayPolygonZone(polygonCoords);
                        } else if (zoneData.from_lat && zoneData.from_lng && zoneData.to_lat && zoneData.to_lng) {
                            displayRectangleZone();
                        }

                        // Add zone name marker
                        addZoneNameMarker(polygonCoords);

                        // Setup event listeners after map is loaded
                        setupMapControls();

                    } catch (error) {
                        console.error('Error initializing map:', error);
                        document.getElementById('zoneMap').innerHTML = '<div class="alert alert-danger m-3">Error loading map: ' +
                            error.message + '</div>';
                    }
                }

                function displayPolygonZone(polygonCoords) {
                    try {
                        console.log('Displaying polygon zone');
                        const polygonPath = polygonCoords.map(coord =>
                            new google.maps.LatLng(coord.lat, coord.lng)
                        );

                        zonePolygon = new google.maps.Polygon({
                            paths: polygonPath,
                            fillColor: '#007bff',
                            fillOpacity: 0.3,
                            strokeColor: '#007bff',
                            strokeWeight: 2,
                            strokeOpacity: 0.8
                        });

                        zonePolygon.setMap(map);

                        // Fit map to polygon bounds
                        const bounds = new google.maps.LatLngBounds();
                        polygonPath.forEach(point => bounds.extend(point));
                        map.fitBounds(bounds);

                        console.log('Polygon displayed successfully');
                    } catch (error) {
                        console.error('Error displaying polygon:', error);
                    }
                }

                function displayRectangleZone() {
                    try {
                        console.log('Displaying rectangle zone');
                        const bounds = new google.maps.LatLngBounds(
                            new google.maps.LatLng(zoneData.from_lat, zoneData.from_lng),
                            new google.maps.LatLng(zoneData.to_lat, zoneData.to_lng)
                        );

                        zoneRectangle = new google.maps.Rectangle({
                            bounds: bounds,
                            fillColor: '#007bff',
                            fillOpacity: 0.3,
                            strokeColor: '#007bff',
                            strokeWeight: 2,
                            strokeOpacity: 0.8
                        });

                        zoneRectangle.setMap(map);
                        map.fitBounds(bounds);

                        console.log('Rectangle displayed successfully');
                    } catch (error) {
                        console.error('Error displaying rectangle:', error);
                    }
                }

                function addZoneNameMarker(polygonCoords) {
                    try {
                        let markerPosition;

                        if (polygonCoords && Array.isArray(polygonCoords) && polygonCoords.length > 0) {
                            // Calculate polygon center
                            const bounds = new google.maps.LatLngBounds();
                            polygonCoords.forEach(coord => {
                                bounds.extend(new google.maps.LatLng(coord.lat, coord.lng));
                            });
                            markerPosition = bounds.getCenter();
                        } else if (zoneData.from_lat && zoneData.from_lng && zoneData.to_lat && zoneData.to_lng) {
                            // Calculate rectangle center
                            markerPosition = new google.maps.LatLng(
                                (zoneData.from_lat + zoneData.to_lat) / 2,
                                (zoneData.from_lng + zoneData.to_lng) / 2
                            );
                        } else {
                            console.log('No valid coordinates for marker');
                            return; // No valid coordinates
                        }

                        const marker = new google.maps.Marker({
                            position: markerPosition,
                            map: map,
                            title: zoneData.name,
                            icon: {
                                path: google.maps.SymbolPath.CIRCLE,
                                scale: 8,
                                fillColor: '#007bff',
                                fillOpacity: 1,
                                strokeColor: '#ffffff',
                                strokeWeight: 2
                            }
                        });

                        const infoWindow = new google.maps.InfoWindow({
                            content: `
                <div style="padding: 5px;">
                    <h6 class="mb-1">${zoneData.name}</h6>
                    <small class="text-muted">Zone ID: ${zoneData.id}</small><br>
                    <small class="text-muted">
                        ${polygonCoords && Array.isArray(polygonCoords) && polygonCoords.length > 0
                            ? `Polygon (${polygonCoords.length} points)`
                            : 'Rectangle Zone'}
                    </small>
                </div>
            `
                        });

                        marker.addListener('click', function() {
                            infoWindow.open(map, marker);
                        });

                        console.log('Marker added successfully');
                    } catch (error) {
                        console.error('Error adding marker:', error);
                    }
                }

                function setupMapControls() {
                    // Map controls
                    const fitBoundsBtn = document.getElementById('fitBounds');
                    const toggleSatelliteBtn = document.getElementById('toggleSatellite');

                    if (fitBoundsBtn) {
                        fitBoundsBtn.addEventListener('click', function() {
                            try {
                                if (zonePolygon) {
                                    const bounds = new google.maps.LatLngBounds();
                                    zonePolygon.getPath().forEach(point => bounds.extend(point));
                                    map.fitBounds(bounds);
                                } else if (zoneRectangle) {
                                    map.fitBounds(zoneRectangle.getBounds());
                                }
                            } catch (error) {
                                console.error('Error fitting bounds:', error);
                            }
                        });
                    }

                    if (toggleSatelliteBtn) {
                        toggleSatelliteBtn.addEventListener('click', function() {
                            try {
                                const currentType = map.getMapTypeId();
                                if (currentType === 'satellite') {
                                    map.setMapTypeId('roadmap');
                                    this.textContent = '{{ __('Satellite') }}';
                                    this.classList.remove('active');
                                } else {
                                    map.setMapTypeId('satellite');
                                    this.textContent = '{{ __('Road') }}';
                                    this.classList.add('active');
                                }
                            } catch (error) {
                                console.error('Error toggling map type:', error);
                            }
                        });
                    }
                }

                // Global error handler for Google Maps
                window.gm_authFailure = function() {
                    console.error('Google Maps authentication failed');
                    document.getElementById('zoneMap').innerHTML =
                        '<div class="alert alert-danger m-3">Google Maps authentication failed. Please check your API key.</div>';
                };

                // Fallback if callback doesn't work
                document.addEventListener('DOMContentLoaded', function() {
                    // Wait a bit for Google Maps to load, then try to initialize if not already done
                    setTimeout(function() {
                        if (typeof map === 'undefined' && typeof google !== 'undefined' && typeof google.maps !==
                            'undefined') {
                            console.log('Fallback initialization');
                            initZoneMap();
                        }
                    }, 2000);
                });
            </script>

            <script async defer
                src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&callback=initZoneMap"
                onerror="console.error('Failed to load Google Maps script'); document.getElementById('zoneMap').innerHTML = '<div class=\'alert alert-danger m-3\'>Failed to load Google Maps. Please check your internet connection.</div>';">
            </script>
        @endpush
    @endif
@endsection
