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
    const selectionMethod = document.querySelector('input[name="selection_method"]:checked').value;

    if (selectionMethod === 'polygon') {
        if (!polygonCoordinates.length) {
            e.preventDefault();
            alert('{{ __("Please draw a polygon on the map or switch to manual coordinates.") }}');
            return false;
        }
    } else {
        // Validate manual coordinates
        const fromLat = document.querySelector('input[name="from_lat"]').value;
        const fromLng = document.querySelector('input[name="from_lng"]').value;
        const toLat = document.querySelector('input[name="to_lat"]').value;
        const toLng = document.querySelector('input[name="to_lng"]').value;

        if (!fromLat || !fromLng || !toLat || !toLng) {
            e.preventDefault();
            alert('{{ __("Please fill in all coordinate fields or switch to polygon drawing.") }}');
            return false;
        }
    }
});
</script>

@if(env('GOOGLE_MAPS_API_KEY'))
<script async defer src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&libraries=drawing&callback=initMap"></script>
@else
<div class="alert alert-warning">
    <strong>{{ __('Google Maps API Key Required') }}</strong><br>
    {{ __('Please configure GOOGLE_MAPS_API_KEY in your .env file to use the map features.') }}
</div>
@endif
@endpush
@endsection
