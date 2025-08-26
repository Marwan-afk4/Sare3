<script>
/**
 * Inline Ride Tracking JavaScript
 * Simple version that doesn't require external files
 */

class SimpleRideTracker {
    constructor(rideData, mapElementId) {
        this.rideData = rideData;
        this.mapElementId = mapElementId;
        this.map = null;
        this.directionsService = null;
        this.directionsRenderer = null;
        this.driverMarker = null;
        this.pickupMarker = null;
        this.dropoffMarker = null;
        this.routePolyline = null;
        this.trackingInterval = null;
    }

    init() {
        console.log('Initializing ride tracker with data:', this.rideData);
        
        // Check if Google Maps is loaded
        if (typeof google === 'undefined' || !google.maps) {
            console.error('Google Maps API not loaded');
            document.getElementById(this.mapElementId).innerHTML = '<div class="alert alert-danger m-3"><strong>Error:</strong> Google Maps API not loaded. Please check your API key configuration.</div>';
            return;
        }

        if (!this.rideData.pickup.lat || !this.rideData.pickup.lng) {
            console.log('No pickup coordinates available');
            document.getElementById(this.mapElementId).innerHTML = '<div class="alert alert-warning m-3">No location data available for this ride.</div>';
            return;
        }

        console.log('Pickup coordinates:', this.rideData.pickup.lat, this.rideData.pickup.lng);
        console.log('Dropoff coordinates:', this.rideData.dropoff.lat, this.rideData.dropoff.lng);

        try {
            this.initMap();
            this.setupMarkers();
            this.handleRideStatus();
            console.log('Map initialized successfully');
        } catch (error) {
            console.error('Error initializing map:', error);
            document.getElementById(this.mapElementId).innerHTML = '<div class="alert alert-danger m-3"><strong>Error:</strong> Failed to initialize map. ' + error.message + '</div>';
        }
    }

    initMap() {
        const mapContainer = document.getElementById(this.mapElementId);
        console.log('Map container:', mapContainer);
        console.log('Container dimensions:', mapContainer.offsetWidth, 'x', mapContainer.offsetHeight);
        
        if (!mapContainer) {
            console.error('Map container not found:', this.mapElementId);
            return;
        }

        // Clear loading indicator
        mapContainer.innerHTML = '';

        if (mapContainer.offsetHeight === 0) {
            console.error('Map container has zero height');
            mapContainer.style.height = '400px';
            console.log('Set container height to 400px');
        }

        try {
            this.map = new google.maps.Map(mapContainer, {
                zoom: 13,
                center: { lat: this.rideData.pickup.lat, lng: this.rideData.pickup.lng },
                mapTypeId: google.maps.MapTypeId.ROADMAP
            });

            console.log('Google Maps instance created:', this.map);
        } catch (error) {
            console.error('Error creating Google Maps instance:', error);
            mapContainer.innerHTML = '<div class="alert alert-danger m-3">Error creating map: ' + error.message + '</div>';
            return;
        }

        this.directionsService = new google.maps.DirectionsService();
        this.directionsRenderer = new google.maps.DirectionsRenderer({
            suppressMarkers: true,
            polylineOptions: {
                strokeColor: '#4285F4',
                strokeWeight: 4
            }
        });
        this.directionsRenderer.setMap(this.map);
        
        // Force map resize after a short delay
        setTimeout(() => {
            google.maps.event.trigger(this.map, 'resize');
            console.log('Map resize triggered');
        }, 100);
    }

    setupMarkers() {
        // Pickup marker - using simple marker to avoid deprecation warnings
        this.pickupMarker = new google.maps.Marker({
            position: { lat: this.rideData.pickup.lat, lng: this.rideData.pickup.lng },
            map: this.map,
            title: 'Pickup Location',
            icon: {
                path: google.maps.SymbolPath.CIRCLE,
                scale: 10,
                fillColor: '#4CAF50',
                fillOpacity: 1,
                strokeColor: 'white',
                strokeWeight: 2
            },
            label: {
                text: 'P',
                color: 'white',
                fontWeight: 'bold'
            }
        });

        // Dropoff marker
        if (this.rideData.dropoff.lat && this.rideData.dropoff.lng) {
            this.dropoffMarker = new google.maps.Marker({
                position: { lat: this.rideData.dropoff.lat, lng: this.rideData.dropoff.lng },
                map: this.map,
                title: 'Dropoff Location',
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: 10,
                    fillColor: '#F44336',
                    fillOpacity: 1,
                    strokeColor: 'white',
                    strokeWeight: 2
                },
                label: {
                    text: 'D',
                    color: 'white',
                    fontWeight: 'bold'
                }
            });
        }
    }

    handleRideStatus() {
        switch (this.rideData.status) {
            case 'completed':
            case 'finshed':
                this.showCompletedRoute();
                break;
            case 'in_progress':
            case 'accepted':
            case 'waiting_user':
                this.showLiveTracking();
                break;
            default:
                this.showStaticRoute();
        }
    }

    showCompletedRoute() {
        if (this.rideData.routePoints && this.rideData.routePoints.length > 0) {
            const routePath = this.rideData.routePoints.map(point => ({
                lat: parseFloat(point.lat),
                lng: parseFloat(point.lng)
            }));

            this.routePolyline = new google.maps.Polyline({
                path: routePath,
                geodesic: true,
                strokeColor: '#4CAF50',
                strokeOpacity: 1.0,
                strokeWeight: 4
            });

            this.routePolyline.setMap(this.map);

            const bounds = new google.maps.LatLngBounds();
            routePath.forEach(point => bounds.extend(point));
            this.map.fitBounds(bounds);
        } else {
            this.showStaticRoute();
        }
    }

    showLiveTracking() {
        if (this.rideData.dropoff.lat && this.rideData.dropoff.lng) {
            const request = {
                origin: { lat: this.rideData.pickup.lat, lng: this.rideData.pickup.lng },
                destination: { lat: this.rideData.dropoff.lat, lng: this.rideData.dropoff.lng },
                travelMode: google.maps.TravelMode.DRIVING
            };

            this.directionsService.route(request, (result, status) => {
                if (status === 'OK') {
                    this.directionsRenderer.setDirections(result);
                }
            });
        }

        // Add driver marker
        this.driverMarker = new google.maps.Marker({
            map: this.map,
            title: 'Driver Location',
            icon: {
                path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW,
                scale: 6,
                fillColor: '#2196F3',
                fillOpacity: 1,
                strokeColor: 'white',
                strokeWeight: 2,
                rotation: 0
            },
            zIndex: 1000
        });

        this.startTracking();
    }

    showStaticRoute() {
        if (this.rideData.dropoff.lat && this.rideData.dropoff.lng) {
            const request = {
                origin: { lat: this.rideData.pickup.lat, lng: this.rideData.pickup.lng },
                destination: { lat: this.rideData.dropoff.lat, lng: this.rideData.dropoff.lng },
                travelMode: google.maps.TravelMode.DRIVING
            };

            this.directionsService.route(request, (result, status) => {
                if (status === 'OK') {
                    this.directionsRenderer.setDirections(result);
                }
            });
        }
    }

    startTracking() {
        this.trackingInterval = setInterval(() => {
            this.fetchDriverLocation();
        }, 5000);
    }

    async fetchDriverLocation() {
        try {
            const response = await fetch(`/api/rides/${this.rideData.id}/driver-location`);
            const data = await response.json();
            
            if (data.lat && data.lng && this.driverMarker) {
                const newPosition = { 
                    lat: parseFloat(data.lat), 
                    lng: parseFloat(data.lng) 
                };
                this.driverMarker.setPosition(newPosition);
            }
        } catch (error) {
            console.error('Error fetching driver location:', error);
        }
    }

    cleanup() {
        if (this.trackingInterval) {
            clearInterval(this.trackingInterval);
        }
    }

    refreshRideData() {
        // Simple refresh implementation
        location.reload();
    }
}

// Make it globally available
window.SimpleRideTracker = SimpleRideTracker;
</script>