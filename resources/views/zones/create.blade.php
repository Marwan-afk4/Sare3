@extends('layouts.app')
@php
	$currentPage = 'zones';
@endphp
@section('title', __('Create Zone'))
@section('content')
<div class="container">
	<h1>{{ __('Create Zone') }}</h1>
	<div class="mb-3">
		<a href="{{ route('zones.index') }}" class="btn btn-secondary btn-sm me-1"> <i class="fa fa-arrow-right"></i> {{__('Back to')}} {{__('Zones')}}</a>
	</div>
	<div class="main-card mb-3 card">
		<div class="card-body">
			<form method='POST' action='{{ route('zones.store') }}' class="needs-validation" novalidate id="zoneForm">
				@csrf
				<x-form-input
					name="name"
					type="text"
					label="{{__('Name')}}"
					required
				/>

				<x-form-input
					name="admin_profit_percentage"
					type="number"
					step="0.01"
					min="0"
					max="100"
					label="{{__('Admin Profit Percentage')}} (%)"
					placeholder="{{__('Leave empty to use global setting')}}"
				/>

				{{-- <div class="mb-3">
					<label class="form-label">{{ __('Zone Area Selection') }}</label>
					<div class="btn-group mb-3" role="group">
						<input type="radio" class="btn-check" name="selection_method" id="polygon_method" value="polygon" checked>
						<label class="btn btn-outline-primary" for="polygon_method">{{ __('Draw Polygon') }}</label>

						<input type="radio" class="btn-check" name="selection_method" id="coordinates_method" value="coordinates">
						<label class="btn btn-outline-primary" for="coordinates_method">{{ __('Manual Coordinates') }}</label>
					</div>
				</div> --}}

				<!-- Google Maps Container -->
				<div id="polygon_section" class="mb-3">
					<label class="form-label">{{ __('Draw Zone Polygon on Map') }}</label>
					<div id="map" style="height: 400px; width: 100%; border: 1px solid #ddd; border-radius: 4px;"></div>
					<small class="form-text text-muted">{{ __('Click on the map to draw a polygon. Click the first point again to complete the polygon.') }}</small>
					<div class="mt-2">
						<button type="button" class="btn btn-sm btn-warning" id="clearPolygon">{{ __('Clear Polygon') }}</button>
						<button type="button" class="btn btn-sm btn-info" id="centerMap">{{ __('Center Map') }}</button>
					</div>
				</div>

				<!-- Manual Coordinates Section -->
				<div id="coordinates_section" style="display: none;">
					<div class="row">
						<div class="col-md-6">
							<x-form-input
								name="from_lat"
								type="text"
								step="any"
								label="{{__('From Lat')}}"
							/>
						</div>
						<div class="col-md-6">
							<x-form-input
								name="from_lng"
								type="text"
								step="any"
								label="{{__('From Lng')}}"
							/>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6">
							<x-form-input
								name="to_lat"
								type="text"
								step="any"
								label="{{__('To Lat')}}"
							/>
						</div>
						<div class="col-md-6">
							<x-form-input
								name="to_lng"
								type="text"
								step="any"
								label="{{__('To Lng')}}"
							/>
						</div>
					</div>
				</div>

				<!-- Hidden field for polygon coordinates -->
				<input type="hidden" name="polygon_coordinates" id="polygon_coordinates">

				<button type='submit' class="btn btn-primary btn-sm me-1">{{ __('Add') }}</button>
			</form>
		</div>
	</div>
</div>

@push('scripts')
<script>
let map;
let drawingManager;
let currentPolygon = null;
let polygonCoordinates = [];

function initMap() {
    // Default center (you can change this to your preferred location)
    const defaultCenter = { lat: 24.7136, lng: 46.6753 }; // Riyadh, Saudi Arabia

    map = new google.maps.Map(document.getElementById('map'), {
        zoom: 11,
        center: defaultCenter,
        mapTypeId: 'roadmap'
    });

    drawingManager = new google.maps.drawing.DrawingManager({
        drawingMode: google.maps.drawing.OverlayType.POLYGON,
        drawingControl: true,
        drawingControlOptions: {
            position: google.maps.ControlPosition.TOP_CENTER,
            drawingModes: ['polygon']
        },
        polygonOptions: {
            fillColor: '#ff0000',
            fillOpacity: 0.3,
            strokeWeight: 2,
            strokeColor: '#ff0000',
            clickable: false,
            editable: true,
            zIndex: 1
        }
    });

    drawingManager.setMap(map);

    google.maps.event.addListener(drawingManager, 'polygoncomplete', function(polygon) {
        // Remove previous polygon if exists
        if (currentPolygon) {
            currentPolygon.setMap(null);
        }

        currentPolygon = polygon;
        updatePolygonCoordinates();

        // Add listener for polygon changes
        google.maps.event.addListener(polygon.getPath(), 'set_at', updatePolygonCoordinates);
        google.maps.event.addListener(polygon.getPath(), 'insert_at', updatePolygonCoordinates);
        google.maps.event.addListener(polygon.getPath(), 'remove_at', updatePolygonCoordinates);

        // Switch to hand mode after drawing
        drawingManager.setDrawingMode(null);
    });
}

function updatePolygonCoordinates() {
    if (currentPolygon) {
        const path = currentPolygon.getPath();
        polygonCoordinates = [];

        for (let i = 0; i < path.getLength(); i++) {
            const point = path.getAt(i);
            polygonCoordinates.push({
                lat: point.lat(),
                lng: point.lng()
            });
        }

        document.getElementById('polygon_coordinates').value = JSON.stringify(polygonCoordinates);

        // Update bounding box coordinates for backward compatibility
        if (polygonCoordinates.length > 0) {
            const bounds = new google.maps.LatLngBounds();
            polygonCoordinates.forEach(coord => {
                bounds.extend(new google.maps.LatLng(coord.lat, coord.lng));
            });

            const ne = bounds.getNorthEast();
            const sw = bounds.getSouthWest();

            document.querySelector('input[name="from_lat"]').value = sw.lat();
            document.querySelector('input[name="from_lng"]').value = sw.lng();
            document.querySelector('input[name="to_lat"]').value = ne.lat();
            document.querySelector('input[name="to_lng"]').value = ne.lng();
        }
    }
}

// Event listeners
document.getElementById('clearPolygon').addEventListener('click', function() {
    if (currentPolygon) {
        currentPolygon.setMap(null);
        currentPolygon = null;
        polygonCoordinates = [];
        document.getElementById('polygon_coordinates').value = '';

        // Clear coordinate fields
        document.querySelector('input[name="from_lat"]').value = '';
        document.querySelector('input[name="from_lng"]').value = '';
        document.querySelector('input[name="to_lat"]').value = '';
        document.querySelector('input[name="to_lng"]').value = '';
    }
    drawingManager.setDrawingMode(google.maps.drawing.OverlayType.POLYGON);
});

document.getElementById('centerMap').addEventListener('click', function() {
    const defaultCenter = { lat: 24.7136, lng: 46.6753 };
    map.setCenter(defaultCenter);
    map.setZoom(11);
});

// Toggle between polygon and manual coordinate input
document.querySelectorAll('input[name="selection_method"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const polygonSection = document.getElementById('polygon_section');
        const coordinatesSection = document.getElementById('coordinates_section');

        if (this.value === 'polygon') {
            polygonSection.style.display = 'block';
            coordinatesSection.style.display = 'none';
        } else {
            polygonSection.style.display = 'none';
            coordinatesSection.style.display = 'block';

            // Clear polygon data when switching to manual
            if (currentPolygon) {
                currentPolygon.setMap(null);
                currentPolygon = null;
            }
            document.getElementById('polygon_coordinates').value = '';
        }
    });
});

// Form validation
document.getElementById('zoneForm').addEventListener('submit', function(e) {
    const polygonData = document.getElementById('polygon_coordinates').value;
    const hasPolygon = polygonData && polygonData !== '[]' && polygonData !== '';

    // Check if we have manual coordinates (if they are visible/used)
    const fromLatInput = document.querySelector('input[name="from_lat"]');
    const fromLngInput = document.querySelector('input[name="from_lng"]');
    const toLatInput = document.querySelector('input[name="to_lat"]');
    const toLngInput = document.querySelector('input[name="to_lng"]');

    const fromLat = fromLatInput ? fromLatInput.value : '';
    const fromLng = fromLngInput ? fromLngInput.value : '';
    const toLat = toLatInput ? toLatInput.value : '';
    const toLng = toLngInput ? toLngInput.value : '';
    const hasManualCoords = fromLat && fromLng && toLat && toLng;

    if (!hasPolygon && !hasManualCoords) {
        e.preventDefault();
        alert('{{ __("Please draw a polygon on the map to define the zone area.") }}');
        return false;
    }
});
</script>

@if(config('services.google_maps.api_key'))
<script>
    // Load Google Maps API dynamically with proper async loading
    (function() {
        const script = document.createElement('script');
        script.src = 'https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key') }}&libraries=drawing&v=3.64&loading=async&callback=initMap';
        script.async = true;
        script.defer = true;
        script.onerror = function() {
            console.error('Failed to load Google Maps API');
            const mapEl = document.getElementById('map');
            if (mapEl) {
                mapEl.innerHTML = '<div class="alert alert-danger m-3" style="margin: 20px !important;"><strong>{{ __('Failed to load Google Maps') }}</strong><br>{{ __('Please check your internet connection and API key.') }}</div>';
            }
        };
        document.head.appendChild(script);
    })();
</script>
@else
<script>
    // Show error message in map container if API key is missing
    function initMap() {
        const mapEl = document.getElementById('map');
        if (mapEl) {
            mapEl.innerHTML = '<div class="alert alert-danger m-3" style="margin: 20px !important;"><strong>{{ __('Google Maps API Key Required') }}</strong><br>{{ __('Please add GOOGLE_MAPS_API_KEY to your .env file to enable map drawing.') }}<br><small class="text-muted">Contact your system administrator to configure the Google Maps API.</small></div>';
        }
    }
    window.addEventListener('load', initMap);
</script>
@endif
@endpush
@endsection
