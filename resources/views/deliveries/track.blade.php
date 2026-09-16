@extends('layouts.app')
@php
    $currentPage = 'deliveries';
    $st = $delivery->status->value;
    $isLive = in_array($st, ['accepted', 'arrived', 'in_progress'], true);
@endphp
@section('title', __('Track Delivery') . ' #' . $delivery->id)

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
    .status-indicator {
        display: inline-block;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        margin-right: 8px;
    }
    .status-live { background-color: #4CAF50; }
    .status-completed { background-color: #2196F3; }
    .status-pending { background-color: #FF9800; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="mb-3">
        <a href="{{ route('deliveries.show', $delivery) }}" class="btn btn-secondary btn-sm">
            <i class="fa fa-arrow-right"></i> {{ __('Back') }}
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <span class="status-indicator {{ $isLive ? 'status-live' : ($st === 'finshed' ? 'status-completed' : 'status-pending') }}"></span>
                        {{ __('Delivery') }} #{{ $delivery->id }}
                    </h5>
                    <span class="badge bg-{{ $st === 'finshed' ? 'success' : (in_array($st, ['rejected','cancelled']) ? 'danger' : 'warning') }}">
                        {{ __(ucfirst($st)) }}
                    </span>
                </div>
                <div class="card-body p-0">
                    <div id="trackingMap"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-body">
                    <h6>{{ __('Details') }}</h6>
                    <p class="mb-1"><strong>{{ __('User') }}:</strong> {{ $delivery->user->name ?? '-' }}</p>
                    <p class="mb-1"><strong>{{ __('Rider') }}:</strong> {{ $delivery->rider->name ?? '-' }}</p>
                    <p class="mb-1"><strong>{{ __('Vehicle') }}:</strong> {{ $delivery->vehicle_type?->label() ?? '-' }}</p>
                    <p class="mb-1"><strong>{{ __('Pickup') }}:</strong> {{ $delivery->pickup_address ?? ($delivery->pickup_lat . ', ' . $delivery->pickup_lng) }}</p>
                    <p class="mb-0"><strong>{{ __('Dropoff') }}:</strong> {{ $delivery->dropoff_address ?? ($delivery->dropoff_lat . ', ' . $delivery->dropoff_lng) }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const deliveryTrack = {
        id: {{ $delivery->id }},
        status: @json($st),
        pickup: {
            lat: {{ $delivery->pickup_lat ?? 'null' }},
            lng: {{ $delivery->pickup_lng ?? 'null' }},
            address: @json($delivery->pickup_address ?? '')
        },
        dropoff: {
            lat: {{ $delivery->dropoff_lat ?? 'null' }},
            lng: {{ $delivery->dropoff_lng ?? 'null' }},
            address: @json($delivery->dropoff_address ?? '')
        },
        trackingUrl: @json(url('/api/deliveries/' . $delivery->id . '/tracking-data')),
        isLive: {{ $isLive ? 'true' : 'false' }}
    };

    let map, pickupMarker, dropoffMarker, riderMarker, toPickupLine, tripLine;

    function drawPath(path, color) {
        return new google.maps.Polyline({
            path: path,
            geodesic: true,
            strokeColor: color,
            strokeOpacity: 0.9,
            strokeWeight: 4,
            map: map,
        });
    }

    function applyTrackingPayload(data) {
        const toPickup = (data.to_pickup_route_points || []).map(p => ({ lat: parseFloat(p.lat), lng: parseFloat(p.lng) }));
        const trip = (data.route_points || []).map(p => ({ lat: parseFloat(p.lat), lng: parseFloat(p.lng) }));

        if (toPickupLine) toPickupLine.setMap(null);
        if (tripLine) tripLine.setMap(null);
        if (toPickup.length) toPickupLine = drawPath(toPickup, '#FF9800');
        if (trip.length) tripLine = drawPath(trip, '#2196F3');

        const live = data.live_rider_location;
        if (live && live.lat && live.lng) {
            const pos = { lat: parseFloat(live.lat), lng: parseFloat(live.lng) };
            if (!riderMarker) {
                riderMarker = new google.maps.Marker({
                    position: pos,
                    map: map,
                    title: '{{ __("Rider") }}',
                });
            } else {
                riderMarker.setPosition(pos);
            }
        }
    }

    function refreshTracking() {
        fetch(deliveryTrack.trackingUrl)
            .then(r => r.json())
            .then(applyTrackingPayload)
            .catch(err => console.warn('Delivery tracking refresh failed', err));
    }

    function initMap() {
        if (!deliveryTrack.pickup.lat || !deliveryTrack.pickup.lng) {
            document.getElementById('trackingMap').innerHTML =
                '<div class="alert alert-warning m-3">{{ __("No location data available for this delivery.") }}</div>';
            return;
        }

        const pickup = { lat: parseFloat(deliveryTrack.pickup.lat), lng: parseFloat(deliveryTrack.pickup.lng) };
        map = new google.maps.Map(document.getElementById('trackingMap'), {
            zoom: 14,
            center: pickup,
            mapTypeId: 'roadmap',
        });

        pickupMarker = new google.maps.Marker({
            position: pickup,
            map: map,
            title: deliveryTrack.pickup.address || '{{ __("Pickup") }}',
            label: 'P',
        });

        const bounds = new google.maps.LatLngBounds();
        bounds.extend(pickup);

        if (deliveryTrack.dropoff.lat && deliveryTrack.dropoff.lng) {
            const dropoff = { lat: parseFloat(deliveryTrack.dropoff.lat), lng: parseFloat(deliveryTrack.dropoff.lng) };
            dropoffMarker = new google.maps.Marker({
                position: dropoff,
                map: map,
                title: deliveryTrack.dropoff.address || '{{ __("Dropoff") }}',
                label: 'D',
            });
            bounds.extend(dropoff);
        }

        map.fitBounds(bounds);
        refreshTracking();

        if (deliveryTrack.isLive) {
            setInterval(refreshTracking, 10000);
        }
    }

    window.initMap = initMap;
</script>

@if(config('services.google_maps.api_key'))
<script async defer
    src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key') }}&callback=initMap">
</script>
@else
<script>
    document.getElementById('trackingMap').innerHTML =
        '<div class="alert alert-danger m-3">{{ __("Google Maps API Key Required") }}</div>';
</script>
@endif
@endpush
