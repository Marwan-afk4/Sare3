@extends('layouts.app')
@php
    $currentPage = 'abnormal-rides';
@endphp
@section('title', __('Abnormal Rides'))

@push('styles')
<style>
    .modal-details-panel::-webkit-scrollbar {
        width: 6px;
    }
    .modal-details-panel::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    .modal-details-panel::-webkit-scrollbar-thumb {
        background: #ccc;
        border-radius: 4px;
    }
    .modal-details-panel::-webkit-scrollbar-thumb:hover {
        background: #aaa;
    }
    .nav-tabs .nav-link {
        font-weight: 600;
        color: #495057;
        border: none;
        border-bottom: 3px solid transparent;
        padding: 12px 20px;
    }
    .nav-tabs .nav-link.active {
        color: #2196F3;
        border-bottom: 3px solid #2196F3;
        background: none;
    }
</style>
@endpush

@section('content')
    <div class="container-fluid">
        <h1 class="mb-4">{{ __('Abnormal Rides Audit') }}</h1>

        <!-- Summary & Tab Navigation Card -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white border-bottom p-0">
                <ul class="nav nav-tabs border-0" id="auditTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link {{ $activeTab === 'multi_captain' ? 'active' : '' }}" 
                           href="{{ route('rides.abnormal', ['active_tab' => 'multi_captain', 'duration_threshold' => $durationThreshold]) }}">
                            <i class="fa fa-users me-2"></i> {{ __('Multi-Captain Rides') }}
                            <span class="badge rounded-pill bg-danger ms-2">{{ $multiCaptainCount }}</span>
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link {{ $activeTab === 'suspicious' ? 'active' : '' }}" 
                           href="{{ route('rides.abnormal', ['active_tab' => 'suspicious', 'duration_threshold' => $durationThreshold]) }}">
                            <i class="fa fa-exclamation-circle me-2"></i> {{ __('Suspicious Rides') }}
                            <span class="badge rounded-pill bg-warning text-dark ms-2">{{ $suspiciousCount }}</span>
                        </a>
                    </li>
                </ul>
            </div>

            @if($activeTab === 'suspicious')
                <div class="card-body bg-light border-bottom py-3">
                    <form action="{{ route('rides.abnormal') }}" method="GET" class="row g-3 align-items-end">
                        <input type="hidden" name="active_tab" value="suspicious">
                        <div class="col-md-4 col-sm-6">
                            <label class="form-label fw-bold">{{ __('Flag completed rides shorter than (minutes)') }}</label>
                            <div class="input-group">
                                <input type="number" name="duration_threshold" min="1" step="1" 
                                       class="form-control" placeholder="e.g. 2" value="{{ $durationThreshold }}">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-sync-alt"></i> {{ __('Update') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            @endif

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="mb-0 table table-hover align-middle">
                        <thead class="table-light">
                            @if($activeTab === 'suspicious')
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('User') }}</th>
                                    <th>{{ __('Driver') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Flag Reason') }}</th>
                                    <th>{{ __('Trip Started') }}</th>
                                    <th>{{ __('Ended At') }}</th>
                                    <th>{{ __('Duration') }}</th>
                                    <th class="text-end">{{ __('Actions') }}</th>
                                </tr>
                            @else
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('User') }}</th>
                                    <th>{{ __('Assigned Driver') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Offers Count') }}</th>
                                    <th>{{ __('Captains Offered') }}</th>
                                    <th>{{ __('Created At') }}</th>
                                    <th class="text-end">{{ __('Actions') }}</th>
                                </tr>
                            @endif
                        </thead>
                        <tbody>
                            @forelse($rides as $ride)
                                @if($activeTab === 'suspicious')
                                    @php
                                        $durationText = '-';
                                        $flagReason = '';
                                        $flagClass = 'bg-secondary';
                                        
                                        if ($ride->status->value === 'cancelled' && $ride->trip_started_at) {
                                            $flagReason = __('Cancelled After Start');
                                            $flagClass = 'bg-danger';
                                            $durationText = __('N/A');
                                        } elseif (in_array($ride->status->value, ['completed', 'finshed', 'finished']) && $ride->trip_started_at) {
                                            $startTime = \Carbon\Carbon::parse($ride->trip_started_at);
                                            $endTime = $ride->completed_at ? \Carbon\Carbon::parse($ride->completed_at) : ($ride->ended_at ? \Carbon\Carbon::parse($ride->ended_at) : null);
                                            if ($endTime) {
                                                $diffInSeconds = $startTime->diffInSeconds($endTime);
                                                if ($diffInSeconds < 60) {
                                                    $durationText = $diffInSeconds . ' ' . __('sec');
                                                } else {
                                                    $durationText = round($diffInSeconds / 60, 1) . ' ' . __('min');
                                                }
                                                $flagReason = __('Abnormally Short Duration');
                                                $flagClass = 'bg-warning text-dark';
                                            }
                                        }
                                    @endphp
                                    <tr>
                                        <td>#{{ $ride->id }}</td>
                                        <td>
                                            @if($ride->user)
                                                <a href="{{ route('users.show', $ride->user->id) }}" class="fw-bold text-decoration-none">
                                                    {{ $ride->user->name }}
                                                </a>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($ride->driver)
                                                <a href="{{ route('drivers.show', $ride->driver->id) }}" class="text-decoration-none text-success">
                                                    {{ $ride->driver->name }}
                                                </a>
                                            @else
                                                <span class="text-muted">{{ __('Not assigned') }}</span>
                                            @endif
                                        </td>
                                        <td>{!! $ride->status->badge() !!}</td>
                                        <td>
                                            <span class="badge {{ $flagClass }} px-2 py-1">
                                                {{ $flagReason }}
                                            </span>
                                        </td>
                                        <td>{{ $ride->trip_started_at ? $ride->trip_started_at->format('M d, Y h:i:s A') : '-' }}</td>
                                        <td>{{ $ride->completed_at ? $ride->completed_at->format('M d, Y h:i:s A') : ($ride->ended_at ? $ride->ended_at->format('M d, Y h:i:s A') : '-') }}</td>
                                        <td class="fw-bold">{{ $durationText }}</td>
                                        <td class="text-end">
                                            <div class="d-flex justify-content-end gap-1">
                                                <button type="button" class="btn btn-subtle-primary btn-sm preview-route-btn" 
                                                        data-bs-toggle="modal" data-bs-target="#routeMapModal"
                                                        data-ride-id="{{ $ride->id }}"
                                                        data-pickup-lat="{{ $ride->pickup_lat ?? 'null' }}"
                                                        data-pickup-lng="{{ $ride->pickup_lng ?? 'null' }}"
                                                        data-pickup-address="{{ $ride->pickup_address }}"
                                                        data-dropoff-lat="{{ $ride->dropoff_lat ?? 'null' }}"
                                                        data-dropoff-lng="{{ $ride->dropoff_lng ?? 'null' }}"
                                                        data-dropoff-address="{{ $ride->dropoff_address }}"
                                                        data-status="{{ $ride->status->value }}"
                                                        data-status-label="{{ $ride->status->label() }}"
                                                        data-status-color="{{ $ride->status->color() }}"
                                                        data-status-text-color="{{ $ride->status->textColor() }}"
                                                        data-user="{{ $ride->user?->name ?? '-' }}"
                                                        data-driver="{{ $ride->driver?->name ?? '-' }}"
                                                        data-fare="${{ $ride->calculated_final_price ?? ($ride->calculated_initial_price ?? '-') }}"
                                                        data-distance="{{ $ride->total_distance_in_km ?? ($ride->estimated_km ?? '') }}">
                                                    <i class="fa fa-map-marked-alt" title="{{ __('Preview Route') }}"></i>
                                                </button>
                                                <a href="{{ route('rides.show', $ride->id) }}" class="btn btn-subtle-secondary btn-sm" title="{{ __('View Details') }}">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @else
                                    <!-- Multi-Captain Tab -->
                                    <tr>
                                        <td>#{{ $ride->id }}</td>
                                        <td>
                                            @if($ride->user)
                                                <a href="{{ route('users.show', $ride->user->id) }}" class="fw-bold text-decoration-none">
                                                    {{ $ride->user->name }}
                                                </a>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($ride->driver)
                                                <a href="{{ route('drivers.show', $ride->driver->id) }}" class="text-decoration-none text-success">
                                                    {{ $ride->driver->name }}
                                                </a>
                                            @else
                                                <span class="text-muted">{{ __('Not assigned') }}</span>
                                            @endif
                                        </td>
                                        <td>{!! $ride->status->badge() !!}</td>
                                        <td>
                                            <span class="badge bg-danger rounded-pill px-3 py-1">
                                                {{ $ride->offers_count }} {{ __('Offers') }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-1">
                                                @foreach($ride->offers as $offer)
                                                    <span class="badge bg-light text-dark border px-2 py-1" style="font-size: 0.75rem;">
                                                        {{ $offer->driver?->name ?? ('Captain #' . $offer->driver_id) }}: 
                                                        <span class="text-{{ $offer->response === 'accepted' ? 'success' : ($offer->response === 'rejected' ? 'danger' : 'warning') }}">
                                                            {{ $offer->responseLabel() }}
                                                        </span>
                                                    </span>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td>{{ $ride->created_at->format('M d, Y h:i A') }}</td>
                                        <td class="text-end">
                                            <div class="d-flex justify-content-end gap-1">
                                                <button type="button" class="btn btn-subtle-primary btn-sm preview-route-btn" 
                                                        data-bs-toggle="modal" data-bs-target="#routeMapModal"
                                                        data-ride-id="{{ $ride->id }}"
                                                        data-pickup-lat="{{ $ride->pickup_lat ?? 'null' }}"
                                                        data-pickup-lng="{{ $ride->pickup_lng ?? 'null' }}"
                                                        data-pickup-address="{{ $ride->pickup_address }}"
                                                        data-dropoff-lat="{{ $ride->dropoff_lat ?? 'null' }}"
                                                        data-dropoff-lng="{{ $ride->dropoff_lng ?? 'null' }}"
                                                        data-dropoff-address="{{ $ride->dropoff_address }}"
                                                        data-status="{{ $ride->status->value }}"
                                                        data-status-label="{{ $ride->status->label() }}"
                                                        data-status-color="{{ $ride->status->color() }}"
                                                        data-status-text-color="{{ $ride->status->textColor() }}"
                                                        data-user="{{ $ride->user?->name ?? '-' }}"
                                                        data-driver="{{ $ride->driver?->name ?? '-' }}"
                                                        data-fare="${{ $ride->calculated_final_price ?? ($ride->calculated_initial_price ?? '-') }}"
                                                        data-distance="{{ $ride->total_distance_in_km ?? ($ride->estimated_km ?? '') }}">
                                                    <i class="fa fa-map-marked-alt" title="{{ __('Preview Route') }}"></i>
                                                </button>
                                                <a href="{{ route('rides.show', $ride->id) }}" class="btn btn-subtle-secondary btn-sm" title="{{ __('View Details') }}">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">
                                        <i class="fa fa-info-circle fa-2x mb-2 d-block"></i>
                                        {{ __('No abnormal ride logs found in this tab.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($rides->hasPages())
                <div class="card-footer bg-white border-top py-3">
                    {{ $rides->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Reusable Split-Pane Route Preview Modal -->
    <div class="modal fade" id="routeMapModal" tabindex="-1" aria-labelledby="routeMapModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
                <div class="modal-header bg-white border-bottom py-3 px-4">
                    <h5 class="modal-title fw-bold text-primary" id="routeMapModalLabel">
                        <i class="fa fa-map-marked-alt text-success me-2"></i>{{ __('Ride Route Preview') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="row g-0">
                        {{-- Left Column: Interactive Map --}}
                        <div class="col-md-8 position-relative" style="height: 550px;">
                            <div id="indexRideMap" style="height: 550px; width: 100%;"></div>
                            <div id="mapLoader" class="position-absolute top-50 start-50 translate-middle text-center bg-white p-3 rounded shadow" style="z-index: 10; border-radius: 8px;">
                                <div class="spinner-border text-primary" role="status"></div>
                                <p class="mt-2 mb-0 small text-muted">{{ __('Loading map & route data...') }}</p>
                            </div>
                        </div>
                        {{-- Right Column: Detailed Ride Sidebar --}}
                        <div class="col-md-4 bg-light border-start d-flex flex-column modal-details-panel" style="height: 550px; overflow-y: auto;">
                            <div class="p-3 border-bottom bg-white">
                                <h5 class="fw-bold mb-1 text-dark" id="modalRideId">Ride #</h5>
                                <div id="modalStatusBadge"></div>
                            </div>
                            <div class="p-3 flex-grow-1">
                                <div class="mb-3">
                                    <small class="text-muted text-uppercase fw-bold d-block mb-2" style="font-size: 0.75rem; letter-spacing: 0.5px;">{{ __('Route Locations') }}</small>
                                    <div class="d-flex align-items-start mb-2">
                                        <span class="badge bg-success me-2 mt-1" style="width: 20px; height: 20px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.75rem;">P</span>
                                        <div>
                                            <strong class="d-block small text-dark">{{ __('Pickup Address') }}</strong>
                                            <span class="small text-muted" id="modalPickupAddress"></span>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-start">
                                        <span class="badge bg-danger me-2 mt-1" style="width: 20px; height: 20px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.75rem;">D</span>
                                        <div>
                                            <strong class="d-block small text-dark">{{ __('Dropoff Address') }}</strong>
                                            <span class="small text-muted" id="modalDropoffAddress"></span>
                                        </div>
                                    </div>
                                </div>
                                
                                <hr class="my-3 text-muted" style="opacity: 0.15;">
                                
                                <div class="mb-3">
                                    <small class="text-muted text-uppercase fw-bold d-block mb-2" style="font-size: 0.75rem; letter-spacing: 0.5px;">{{ __('Details') }}</small>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <span class="text-muted d-block small" style="font-size: 0.75rem;">{{ __('Passenger') }}</span>
                                            <strong class="small text-dark" id="modalUserName">-</strong>
                                        </div>
                                        <div class="col-6">
                                            <span class="text-muted d-block small" style="font-size: 0.75rem;">{{ __('Driver') }}</span>
                                            <strong class="small text-dark" id="modalDriverName">-</strong>
                                        </div>
                                        <div class="col-6 mt-2">
                                            <span class="text-muted d-block small" style="font-size: 0.75rem;">{{ __('Distance') }}</span>
                                            <strong class="small text-dark" id="modalDistance">-</strong>
                                        </div>
                                        <div class="col-6 mt-2">
                                            <span class="text-muted d-block small" style="font-size: 0.75rem;">{{ __('Fare') }}</span>
                                            <strong class="small text-dark" id="modalFare">-</strong>
                                        </div>
                                    </div>
                                </div>

                                <hr class="my-3 text-muted" style="opacity: 0.15;">

                                <div>
                                    <small class="text-muted text-uppercase fw-bold d-block mb-2" style="font-size: 0.75rem; letter-spacing: 0.5px;">{{ __('Captain Offer History') }}</small>
                                    <div id="modalOfferLogs">
                                        <!-- Dynamically populated offer records -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
let previewMap = null;
let pickupMarker = null;
let dropoffMarker = null;
let routePolyline = null;
let toPickupPolyline = null;
let activeInterval = null;
let activeRideId = null;
let activeRideData = null;

// Callback required by Google Maps SDK
function initMap() {
    // Left empty: map initialization is deferred to when modal is actually shown
}

document.addEventListener('DOMContentLoaded', () => {
    const routeMapModalEl = document.getElementById('routeMapModal');
    if (routeMapModalEl) {
        routeMapModalEl.addEventListener('shown.bs.modal', async (event) => {
            const button = event.relatedTarget;
            if (!button) return;

            const rideId = button.dataset.rideId;
            const pickupLat = parseFloat(button.dataset.pickupLat);
            const pickupLng = parseFloat(button.dataset.pickupLng);
            const pickupAddr = button.dataset.pickupAddress;
            const dropoffLat = parseFloat(button.dataset.dropoffLat);
            const dropoffLng = parseFloat(button.dataset.dropoffLng);
            const dropoffAddr = button.dataset.dropoffAddress;
            const statusLabel = button.dataset.statusLabel;
            const statusColor = button.dataset.statusColor;
            const statusTextColor = button.dataset.statusTextColor;
            const statusVal = button.dataset.status;
            const userVal = button.dataset.user;
            const driverVal = button.dataset.driver;
            const fareVal = button.dataset.fare;
            const distanceVal = button.dataset.distance;

            activeRideId = rideId;
            activeRideData = {
                id: rideId,
                pickup: { lat: pickupLat, lng: pickupLng, address: pickupAddr },
                dropoff: { lat: dropoffLat, lng: dropoffLng, address: dropoffAddr },
                status: statusVal
            };

            document.getElementById('modalRideId').textContent = '{{ __('Ride') }} #' + rideId;
            document.getElementById('modalPickupAddress').textContent = pickupAddr || '-';
            document.getElementById('modalDropoffAddress').textContent = dropoffAddr || '{{ __('No drop-off location selected') }}';
            document.getElementById('modalUserName').textContent = userVal || '-';
            document.getElementById('modalDriverName').textContent = driverVal || '{{ __('Not assigned') }}';
            document.getElementById('modalFare').textContent = fareVal || '-';
            document.getElementById('modalDistance').textContent = distanceVal ? (distanceVal + ' km') : '-';
            
            const badgeEl = document.getElementById('modalStatusBadge');
            badgeEl.className = 'badge rounded-pill px-3 py-2 mt-1';
            badgeEl.style.backgroundColor = '#' + statusColor;
            badgeEl.style.color = '#' + statusTextColor;
            badgeEl.textContent = statusLabel;

            document.getElementById('modalOfferLogs').innerHTML = '<div class="text-center text-muted py-3"><i class="fa fa-spinner fa-spin"></i> {{ __('Loading offer history...') }}</div>';

            const loader = document.getElementById('mapLoader');
            if (loader) loader.classList.remove('d-none');

            initOrCenterMap(pickupLat, pickupLng);

            await loadRouteDetails(rideId, pickupLat, pickupLng, dropoffLat, dropoffLng, statusVal);

            if (['in_progress', 'accepted', 'waiting_user'].includes(statusVal)) {
                activeInterval = setInterval(() => {
                    if (activeRideId === rideId) {
                        loadRouteDetails(rideId, pickupLat, pickupLng, dropoffLat, dropoffLng, statusVal);
                    }
                }, 15000);
            }
        });

        routeMapModalEl.addEventListener('hidden.bs.modal', () => {
            if (activeInterval) {
                clearInterval(activeInterval);
                activeInterval = null;
            }
            activeRideId = null;
            activeRideData = null;

            if (pickupMarker) pickupMarker.setMap(null);
            if (dropoffMarker) dropoffMarker.setMap(null);
            if (routePolyline) routePolyline.setMap(null);
            if (toPickupPolyline) toPickupPolyline.setMap(null);

            pickupMarker = null;
            dropoffMarker = null;
            routePolyline = null;
            toPickupPolyline = null;
        });
    }
});

function initOrCenterMap(lat, lng) {
    const mapElement = document.getElementById('indexRideMap');
    if (!mapElement) return;

    if (!previewMap) {
        previewMap = new google.maps.Map(mapElement, {
            zoom: 13,
            center: { lat: lat, lng: lng },
            mapTypeId: google.maps.MapTypeId.ROADMAP
        });
    } else {
        previewMap.setCenter({ lat: lat, lng: lng });
        previewMap.setZoom(13);
        google.maps.event.trigger(previewMap, 'resize');
    }
}

async function loadRouteDetails(rideId, pickupLat, pickupLng, dropoffLat, dropoffLng, status) {
    try {
        const response = await fetch(`/api/rides/${rideId}/tracking-data`);
        const loader = document.getElementById('mapLoader');
        
        if (loader) loader.classList.add('d-none');

        if (!response.ok) throw new Error('API request failed');
        const data = await response.json();

        if (activeRideId !== rideId) return;

        const offersContainer = document.getElementById('modalOfferLogs');
        offersContainer.innerHTML = '';

        if (!data.offers || data.offers.length === 0) {
            offersContainer.innerHTML = '<div class="small text-muted py-2">{{ __('No offer records found.') }}</div>';
        } else {
            data.offers.forEach(offer => {
                const logDiv = document.createElement('div');
                logDiv.className = 'd-flex justify-content-between align-items-center mb-2 pb-2 border-bottom';
                logDiv.innerHTML = `
                    <div>
                        <strong class="d-block small text-dark">${offer.driver_name}</strong>
                        <span class="badge bg-${offer.response_color} px-2 py-0.5" style="font-size: 0.65rem;">${offer.response_label}</span>
                    </div>
                    <div class="text-end">
                        <span class="d-block small text-muted">${offer.offered_at || '-'}</span>
                        <small class="text-muted" style="font-size: 0.7rem;">${offer.response_seconds != null ? offer.response_seconds + 's' : ''}</small>
                    </div>
                `;
                offersContainer.appendChild(logDiv);
            });
        }

        if (pickupMarker) pickupMarker.setMap(null);
        if (dropoffMarker) dropoffMarker.setMap(null);
        if (routePolyline) routePolyline.setMap(null);
        if (toPickupPolyline) toPickupPolyline.setMap(null);

        pickupMarker = new google.maps.Marker({
            position: { lat: pickupLat, lng: pickupLng },
            map: previewMap,
            title: 'Pickup Location',
            icon: {
                path: google.maps.SymbolPath.CIRCLE,
                scale: 8,
                fillColor: '#4CAF50',
                fillOpacity: 1,
                strokeColor: 'white',
                strokeWeight: 2
            },
            zIndex: 800
        });

        if (dropoffLat && dropoffLng) {
            dropoffMarker = new google.maps.Marker({
                position: { lat: dropoffLat, lng: dropoffLng },
                map: previewMap,
                title: 'Dropoff Location',
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: 8,
                    fillColor: '#F44336',
                    fillOpacity: 1,
                    strokeColor: 'white',
                    strokeWeight: 2
                },
                zIndex: 800
            });
        }

        const bounds = new google.maps.LatLngBounds();
        bounds.extend({ lat: pickupLat, lng: pickupLng });
        if (dropoffLat && dropoffLng) bounds.extend({ lat: dropoffLat, lng: dropoffLng });

        if (data.to_pickup_route_points && data.to_pickup_route_points.length > 0) {
            const toPickupPath = data.to_pickup_route_points.map(p => ({
                lat: parseFloat(p.lat),
                lng: parseFloat(p.lng)
            }));
            toPickupPolyline = new google.maps.Polyline({
                path: toPickupPath,
                geodesic: true,
                strokeColor: '#FF9800',
                strokeOpacity: 0,
                strokeWeight: 4,
                icons: [{
                    icon: { path: 'M 0,-1 0,1', strokeOpacity: 1, strokeColor: '#FF9800', scale: 3 },
                    offset: '0',
                    repeat: '12px'
                }]
            });
            toPickupPolyline.setMap(previewMap);
            toPickupPath.forEach(pt => bounds.extend(pt));
        }

        if (data.trip_route_points && data.trip_route_points.length > 0) {
            const tripPath = data.trip_route_points.map(p => ({
                lat: parseFloat(p.lat),
                lng: parseFloat(p.lng)
            }));
            routePolyline = new google.maps.Polyline({
                path: tripPath,
                geodesic: true,
                strokeColor: '#4CAF50',
                strokeOpacity: 0.8,
                strokeWeight: 4
            });
            routePolyline.setMap(previewMap);
            tripPath.forEach(pt => bounds.extend(pt));
        }

        previewMap.fitBounds(bounds);
    } catch (err) {
        console.warn('Failed to load route preview:', err);
    }
}
</script>

<!-- Google Maps API -->
@if (config('services.google_maps.api_key') && config('services.google_maps.api_key') !== 'your_actual_api_key_here')
    <script async defer
        src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key') }}&callback=initMap&libraries=marker">
    </script>
@else
    <script>
        function initMap() {
            document.getElementById('indexRideMap').innerHTML =
                '<div class="alert alert-warning m-3"><strong>Configuration Required:</strong> Please set your Google Maps API key in the .env file.</div>';
        }
        window.addEventListener('load', initMap);
    </script>
@endif
@endpush
