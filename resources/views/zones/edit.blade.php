@extends('layouts.app')
@php
	$currentPage = 'zones';
@endphp
@section('title', __('Edit Zone'))
@section('content')
<div class="container">
	<h1>{{ __('Edit Zone') }}</h1>
	<div class="mb-3">
		<a href="{{ route('zones.index') }}" class="btn btn-secondary btn-sm me-1"> <i class="fa fa-arrow-right"></i> {{__('Back to')}} {{__('Zones')}}</a>
		{{-- <a href='{{ route('zones.show', $zone) }}' class="btn btn-primary btn-sm me-1">{{ __("Details") }} <i class="fa fa-eye"></i></a> --}}
	</div>
	<div class="main-card mb-3 card">
		<div class="card-body">
			<form method='POST' action='{{ route('zones.update', $zone->id) }}' class="needs-validation" novalidate id="zoneEditForm">
				@csrf
				@method('PUT')
				<x-form-input
					name="name"
					type="text"
					label="{{__('Name')}}"
					:value="$zone->name ?? ''"
					required
				/>

			<!-- Google Maps Container - Always Visible -->
			<div id="polygon_section" class="mb-3">
				<label class="form-label">{{ __('Edit Zone Area on Map') }}</label>
				<div id="map" style="height: 500px; width: 100%; border: 1px solid #ddd; border-radius: 4px;"></div>
				<small class="form-text text-muted">
					{{ __('Click and drag the polygon points to reshape the zone, or draw a new polygon on the map.') }}
				</small>
				<div class="mt-2">
					<button type="button" class="btn btn-sm btn-warning" id="clearPolygon">{{ __('Clear') }}</button>
					<button type="button" class="btn btn-sm btn-success" id="drawNewPolygon">{{ __('Draw New Polygon') }}</button>
					<button type="button" class="btn btn-sm btn-info" id="centerMap">{{ __('Center Map') }}</button>
				</div>
			</div>

			<!-- Manual Coordinates Section - Hidden by default, auto-updated by map -->
			<div id="coordinates_section" style="display: none;">
					<div class="row">
						<div class="col-md-6">
							<x-form-input
								name="from_lat"
								type="number"
								step="any"
								label="{{__('From Lat')}}"
								:value="$zone->from_lat ?? ''"
							/>
						</div>
						<div class="col-md-6">
							<x-form-input
								name="from_lng"
								type="number"
								step="any"
								label="{{__('From Lng')}}"
								:value="$zone->from_lng ?? ''"
							/>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6">
							<x-form-input
								name="to_lat"
								type="number"
								step="any"
								label="{{__('To Lat')}}"
								:value="$zone->to_lat ?? ''"
							/>
						</div>
						<div class="col-md-6">
							<x-form-input
								name="to_lng"
								type="number"
								step="any"
								label="{{__('To Lng')}}"
								:value="$zone->to_lng ?? ''"
							/>
						</div>
					</div>
				</div>

				<!-- Hidden field for polygon coordinates -->
				<input type="hidden" name="polygon_coordinates" id="polygon_coordinates" value="{{ $zone->polygon_coordinates ? json_encode($zone->polygon_coordinates) : '' }}">

				<button type='submit' class="btn btn-warning btn-sm me-1">{{ __('Save') }}</button>
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

// Existing polygon data from server - with proper parsing
let existingPolygonData = @json($zone->polygon_coordinates ?? []);

// Debug logging
console.log('Zone ID:', {{ $zone->id ?? 'null' }});
console.log('Raw polygon data type:', typeof existingPolygonData);
console.log('Raw polygon data:', existingPolygonData);

// Ensure existingPolygonData is always an array
if (typeof existingPolygonData === 'string') {
    console.log('Polygon data is a string, attempting to parse...');
    try {
        existingPolygonData = JSON.parse(existingPolygonData);
        console.log('Successfully parsed polygon data:', existingPolygonData);
    } catch (e) {
        console.error('Error parsing polygon coordinates:', e);
        existingPolygonData = [];
    }
}

// Additional safety check - ensure it's an array
if (!Array.isArray(existingPolygonData)) {
    console.warn('Polygon coordinates is not an array (type: ' + typeof existingPolygonData + '), converting to empty array');
    console.log('Non-array polygon data value:', existingPolygonData);
    existingPolygonData = [];
} else {
    console.log('Polygon data is an array with', existingPolygonData.length, 'points');
}

// If no polygon data but we have rectangle coordinates, convert to polygon
const hasRectangleCoords = {{ $zone->from_lat ?? 'null' }} !== null && {{ $zone->from_lng ?? 'null' }} !== null;
if (existingPolygonData.length === 0 && hasRectangleCoords) {
    const fromLat = {{ $zone->from_lat ?? 0 }};
    const fromLng = {{ $zone->from_lng ?? 0 }};
    const toLat = {{ $zone->to_lat ?? 0 }};
    const toLng = {{ $zone->to_lng ?? 0 }};
    
    // Convert rectangle to polygon (4 corners)
    existingPolygonData = [
        { lat: fromLat, lng: fromLng },
        { lat: fromLat, lng: toLng },
        { lat: toLat, lng: toLng },
        { lat: toLat, lng: fromLng }
    ];
}

function initMap() {
    // Default center or use existing data center
    let defaultCenter = { lat: 24.7136, lng: 46.6753 }; // Riyadh, Saudi Arabia

    // Calculate center from existing data
    if (Array.isArray(existingPolygonData) && existingPolygonData.length > 0) {
        const bounds = new google.maps.LatLngBounds();
        existingPolygonData.forEach(coord => {
            bounds.extend(new google.maps.LatLng(coord.lat, coord.lng));
        });
        defaultCenter = bounds.getCenter().toJSON();
    }

    map = new google.maps.Map(document.getElementById('map'), {
        zoom: 12,
        center: defaultCenter,
        mapTypeId: 'roadmap'
    });

    drawingManager = new google.maps.drawing.DrawingManager({
        drawingMode: null,
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

    // Load existing polygon if available
    if (Array.isArray(existingPolygonData) && existingPolygonData.length > 0) {
        loadExistingPolygon();
    }

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

function loadExistingPolygon() {
    if (Array.isArray(existingPolygonData) && existingPolygonData.length > 0) {
        const polygonPath = existingPolygonData.map(coord =>
            new google.maps.LatLng(coord.lat, coord.lng)
        );

        currentPolygon = new google.maps.Polygon({
            paths: polygonPath,
            fillColor: '#ff0000',
            fillOpacity: 0.3,
            strokeWeight: 2,
            strokeColor: '#ff0000',
            editable: true,
            draggable: false
        });

        currentPolygon.setMap(map);
        polygonCoordinates = [...existingPolygonData];

        // Add listeners for polygon changes
        google.maps.event.addListener(currentPolygon.getPath(), 'set_at', updatePolygonCoordinates);
        google.maps.event.addListener(currentPolygon.getPath(), 'insert_at', updatePolygonCoordinates);
        google.maps.event.addListener(currentPolygon.getPath(), 'remove_at', updatePolygonCoordinates);

        // Fit map to polygon bounds
        const bounds = new google.maps.LatLngBounds();
        polygonPath.forEach(point => bounds.extend(point));
        map.fitBounds(bounds);
    }
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
});

document.getElementById('drawNewPolygon').addEventListener('click', function() {
    drawingManager.setDrawingMode(google.maps.drawing.OverlayType.POLYGON);
});

document.getElementById('centerMap').addEventListener('click', function() {
    if (currentPolygon && polygonCoordinates.length > 0) {
        const bounds = new google.maps.LatLngBounds();
        polygonCoordinates.forEach(coord => {
            bounds.extend(new google.maps.LatLng(coord.lat, coord.lng));
        });
        map.fitBounds(bounds);
    } else {
        const defaultCenter = { lat: 24.7136, lng: 46.6753 };
        map.setCenter(defaultCenter);
        map.setZoom(11);
    }
});

// Toggle between polygon and manual coordinate input (if radio buttons are present)
const selectionMethodRadios = document.querySelectorAll('input[name="selection_method"]');
if (selectionMethodRadios.length > 0) {
    selectionMethodRadios.forEach(radio => {
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
}

// Form validation
document.getElementById('zoneEditForm').addEventListener('submit', function(e) {
    const polygonData = document.getElementById('polygon_coordinates').value;
    const hasPolygon = polygonData && polygonData !== '[]' && polygonData !== '';
    
    // Check if we have either polygon or manual coordinates
    const fromLat = document.querySelector('input[name="from_lat"]').value;
    const fromLng = document.querySelector('input[name="from_lng"]').value;
    const toLat = document.querySelector('input[name="to_lat"]').value;
    const toLng = document.querySelector('input[name="to_lng"]').value;
    const hasManualCoords = fromLat && fromLng && toLat && toLng;

    if (!hasPolygon && !hasManualCoords) {
        e.preventDefault();
        alert('{{ __("Please draw a polygon on the map to define the zone area.") }}');
        return false;
    }
});
</script>

@if(env('GOOGLE_MAPS_API_KEY'))
<script>
    // Load Google Maps API dynamically with proper async loading
    (function() {
        const script = document.createElement('script');
        script.src = 'https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&libraries=drawing&loading=async&callback=initMap';
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
            mapEl.innerHTML = '<div class="alert alert-danger m-3" style="margin: 20px !important;"><strong>{{ __('Google Maps API Key Required') }}</strong><br>{{ __('Please add GOOGLE_MAPS_API_KEY to your .env file to enable map editing.') }}<br><small class="text-muted">Contact your system administrator to configure the Google Maps API.</small></div>';
        }
    }
    window.addEventListener('load', initMap);
</script>
@endif
@endpush
@endsection
