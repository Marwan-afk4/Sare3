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
        // Pickup marker
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

        // Add info window for pickup
        const pickupInfoWindow = new google.maps.InfoWindow({
            content: `<div><strong>Pickup Location</strong><br>${this.rideData.pickup.address || 'Pickup Point'}</div>`
        });
        this.pickupMarker.addListener('click', () => {
            pickupInfoWindow.open(this.map, this.pickupMarker);
        });

        // Dropoff marker (if available)
        if (this.rideData.dropoff.lat && this.rideData.dropoff.lng) {
            this.dropoffMarker = new google.maps.Marker({
                position: { lat: this.rideData.dropoff.lat, lng: this.rideData.dropoff.lng },
                map: this.map,
                title: 'Drop-off Location',
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: 12,
                    fillColor: '#F44336',
                    fillOpacity: 1,
                    strokeColor: 'white',
                    strokeWeight: 3
                },
                label: {
                    text: 'D',
                    color: 'white',
                    fontWeight: 'bold'
                }
            });

            // Add info window for dropoff
            const dropoffInfoWindow = new google.maps.InfoWindow({
                content: `<div><strong>Drop-off Location</strong><br>${this.rideData.dropoff.address || 'Destination'}</div>`
            });
            this.dropoffMarker.addListener('click', () => {
                dropoffInfoWindow.open(this.map, this.dropoffMarker);
            });
        } else {
            // Show message when no drop-off location is set
            this.showNoDropoffMessage();
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
        // Start Firebase real-time tracking for in-progress rides
        if (this.rideData.status === 'in_progress') {
            this.startFirebaseTracking();
        } else {
            // Use API polling for other active statuses
            this.startApiPolling();
        }
    }

    startFirebaseTracking() {
        try {
            if (typeof firebase === 'undefined' || !firebase.database) {
                console.log('Firebase not available, falling back to API polling');
                this.startApiPolling();
                return;
            }

            const firebaseRideId = this.rideData.firebaseRideId || `ride_${this.rideData.id}`;
            this.firebaseRef = firebase.database().ref(`rides/${firebaseRideId}/driver_location`);

            console.log('Starting Firebase tracking for:', firebaseRideId);

            this.firebaseRef.on('value', (snapshot) => {
                const location = snapshot.val();
                console.log('Firebase location update:', location);
                
                if (location && location.lat && location.lng && this.driverMarker) {
                    const newPosition = {
                        lat: parseFloat(location.lat),
                        lng: parseFloat(location.lng)
                    };
                    
                    this.updateDriverPosition(newPosition);
                    
                    // Update timestamp if available
                    if (location.timestamp) {
                        this.lastLocationUpdate = new Date(location.timestamp);
                    }
                }
            });

            // Also check ride status changes to stop tracking when completed
            const rideStatusRef = firebase.database().ref(`rides/${firebaseRideId}/status`);
            rideStatusRef.on('value', (snapshot) => {
                const status = snapshot.val();
                if (status && (status === 'completed' || status === 'finished')) {
                    console.log('Ride completed, stopping Firebase tracking');
                    this.stopTracking();
                }
            });

        } catch (error) {
            console.error('Firebase tracking error:', error);
            this.startApiPolling();
        }
    }

    startApiPolling() {
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
                this.updateDriverPosition(newPosition);
            }

            // Check if ride status changed to completed
            if (data.status && (data.status === 'completed' || data.status === 'finished')) {
                console.log('Ride completed, stopping tracking');
                this.stopTracking();
            }
        } catch (error) {
            console.error('Error fetching driver location:', error);
        }
    }

    updateDriverPosition(newPosition) {
        if (!this.driverMarker) return;

        const currentPosition = this.driverMarker.getPosition();
        
        if (currentPosition) {
            // Animate marker movement for smooth transition
            this.animateMarker(this.driverMarker, currentPosition, newPosition);
        } else {
            // First position update
            this.driverMarker.setPosition(newPosition);
        }

        // Keep driver in view if they move too far
        const bounds = this.map.getBounds();
        if (bounds && !bounds.contains(newPosition)) {
            this.map.panTo(newPosition);
        }
    }

    animateMarker(marker, startPos, endPos) {
        const startLat = startPos.lat();
        const startLng = startPos.lng();
        const endLat = endPos.lat;
        const endLng = endPos.lng;

        let step = 0;
        const numSteps = 30;
        const timePerStep = 100;

        const stepLat = (endLat - startLat) / numSteps;
        const stepLng = (endLng - startLng) / numSteps;

        const animate = () => {
            if (step <= numSteps) {
                const lat = startLat + (stepLat * step);
                const lng = startLng + (stepLng * step);
                marker.setPosition({ lat, lng });
                step++;
                setTimeout(animate, timePerStep);
            }
        };

        animate();
    }

    stopTracking() {
        // Stop Firebase listening
        if (this.firebaseRef) {
            this.firebaseRef.off();
            this.firebaseRef = null;
            console.log('Firebase tracking stopped');
        }

        // Stop API polling
        if (this.trackingInterval) {
            clearInterval(this.trackingInterval);
            this.trackingInterval = null;
            console.log('API polling stopped');
        }
    }

    showNoDropoffMessage() {
        // Create info window to show no drop-off message
        const noDropoffInfoWindow = new google.maps.InfoWindow({
            content: `<div class="alert alert-warning mb-0">
                        <strong>No Drop-off Location</strong><br>
                        No drop-off location has been selected for this ride.
                      </div>`,
            position: { lat: this.rideData.pickup.lat, lng: this.rideData.pickup.lng }
        });
        
        noDropoffInfoWindow.open(this.map);
        
        // Note: Pickup marker is already shown in setupMarkers(), 
        // so we don't need to create another marker here
    }

    cleanup() {
        this.stopTracking();
    }

    refreshRideData() {
        // Simple refresh implementation
        location.reload();
    }
}

// Make it globally available
window.SimpleRideTracker = SimpleRideTracker;
</script>