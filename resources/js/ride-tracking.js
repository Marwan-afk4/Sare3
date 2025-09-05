/**
 * Real-time ride tracking with Firebase integration
 */

class RideTracker {
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
        this.firebaseRef = null;

        // Firebase configuration (if available)
        this.firebaseConfig = {
            databaseURL: 'https://sarea-adce3-default-rtdb.firebaseio.com'
        };
    }

    /**
     * Initialize the map and tracking
     */
    init() {
        if (!this.rideData.pickup.lat || !this.rideData.pickup.lng) {
            console.log('No pickup coordinates available');
            return;
        }

        this.initMap();
        this.setupMarkers();
        this.handleRideStatus();
    }

    /**
     * Initialize Google Maps
     */
    initMap() {
        this.map = new google.maps.Map(document.getElementById(this.mapElementId), {
            zoom: 13,
            center: { lat: this.rideData.pickup.lat, lng: this.rideData.pickup.lng },
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            styles: [
                {
                    featureType: 'poi',
                    elementType: 'labels',
                    stylers: [{ visibility: 'off' }]
                }
            ]
        });

        this.directionsService = new google.maps.DirectionsService();
        this.directionsRenderer = new google.maps.DirectionsRenderer({
            suppressMarkers: true,
            polylineOptions: {
                strokeColor: '#4285F4',
                strokeWeight: 4,
                strokeOpacity: 0.8
            }
        });
        this.directionsRenderer.setMap(this.map);
    }

    /**
     * Setup map markers - Show both pickup and drop-off markers
     */
    setupMarkers() {
        // Pickup marker
        this.pickupMarker = new google.maps.Marker({
            position: { lat: this.rideData.pickup.lat, lng: this.rideData.pickup.lng },
            map: this.map,
            title: 'Pickup Location',
            icon: this.createMarkerIcon('#4CAF50', 'P')
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
                icon: this.createMarkerIcon('#F44336', 'D')
            });

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

    /**
     * Create custom marker icon
     */
    createMarkerIcon(color, text) {
        return {
            url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                <svg width="32" height="32" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="16" cy="16" r="12" fill="${color}" stroke="white" stroke-width="2"/>
                    <text x="16" y="20" text-anchor="middle" fill="white" font-size="12" font-weight="bold">${text}</text>
                </svg>
            `),
            scaledSize: new google.maps.Size(32, 32),
            anchor: new google.maps.Point(16, 16)
        };
    }

    /**
     * Handle different ride statuses
     */
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

    /**
     * Show completed ride route using filtered/snapped points
     */
    async showCompletedRoute() {
        try {
            // Fetch filtered/snapped route points from API
            const response = await fetch(`/api/rides/${this.rideData.id}/route-points`);
            const data = await response.json();

            console.log('Route points response:', data);

            // Use filtered points if available, fallback to raw points
            const points = (data.points && data.points.length > 0)
                ? data.points
                : (this.rideData.routePoints || []);

            if (points.length > 0) {
                const routePath = points.map(point => ({
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

                // Add markers for start and end points
                if (routePath.length > 0) {
                    new google.maps.Marker({
                        position: routePath[0],
                        map: this.map,
                        title: 'Trip Start',
                        icon: this.createMarkerIcon('#2196F3', 'S')
                    });

                    new google.maps.Marker({
                        position: routePath[routePath.length - 1],
                        map: this.map,
                        title: 'Trip End',
                        icon: this.createMarkerIcon('#FF9800', 'E')
                    });
                }

                // Fit map to show entire route
                const bounds = new google.maps.LatLngBounds();
                routePath.forEach(point => bounds.extend(point));
                if (this.pickupMarker) bounds.extend(this.pickupMarker.getPosition());
                if (this.dropoffMarker) bounds.extend(this.dropoffMarker.getPosition());
                this.map.fitBounds(bounds);

                // Log filtering results
                if (data.total_points && data.filtered_points) {
                    console.log(`Route filtered: ${data.total_points} -> ${data.filtered_points} points (snapped: ${data.snapped})`);
                }
            } else {
                console.log('No route points available, showing static route');
                this.showStaticRoute();
            }
        } catch (error) {
            console.error('Error fetching route points:', error);
            // Fallback to original method
            this.showCompletedRouteFallback();
        }
    }

    /**
     * Fallback method for showing completed route (original implementation)
     */
    showCompletedRouteFallback() {
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

            // Add markers for start and end points
            if (routePath.length > 0) {
                new google.maps.Marker({
                    position: routePath[0],
                    map: this.map,
                    title: 'Trip Start',
                    icon: this.createMarkerIcon('#2196F3', 'S')
                });

                new google.maps.Marker({
                    position: routePath[routePath.length - 1],
                    map: this.map,
                    title: 'Trip End',
                    icon: this.createMarkerIcon('#FF9800', 'E')
                });
            }

            // Fit map to show entire route
            const bounds = new google.maps.LatLngBounds();
            routePath.forEach(point => bounds.extend(point));
            if (this.pickupMarker) bounds.extend(this.pickupMarker.getPosition());
            if (this.dropoffMarker) bounds.extend(this.dropoffMarker.getPosition());
            this.map.fitBounds(bounds);
        } else {
            this.showStaticRoute();
        }
    }

    /**
     * Show live tracking for active rides
     */
    showLiveTracking() {
        // Show planned route
        if (this.rideData.dropoff.lat && this.rideData.dropoff.lng) {
            const request = {
                origin: { lat: this.rideData.pickup.lat, lng: this.rideData.pickup.lng },
                destination: { lat: this.rideData.dropoff.lat, lng: this.rideData.dropoff.lng },
                travelMode: google.maps.TravelMode.DRIVING,
                avoidHighways: false,
                avoidTolls: false
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
            icon: this.createDriverIcon(),
            zIndex: 1000
        });

        // Start real-time tracking
        this.startRealTimeTracking();
    }

    /**
     * Create animated driver icon
     */
    createDriverIcon() {
        return {
            url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                <svg width="40" height="40" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="20" cy="20" r="18" fill="#2196F3" stroke="white" stroke-width="3"/>
                    <circle cx="20" cy="20" r="12" fill="white" opacity="0.3"/>
                    <polygon points="20,10 25,18 15,18" fill="white"/>
                </svg>
            `),
            scaledSize: new google.maps.Size(40, 40),
            anchor: new google.maps.Point(20, 20)
        };
    }

    /**
     * Show static route for pending rides
     */
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

    /**
     * Start real-time tracking - Firebase for in-progress rides, API polling for others
     */
    startRealTimeTracking() {
        // Use Firebase real-time tracking for in-progress rides
        if (this.rideData.status === 'in_progress') {
            this.startFirebaseTracking();
        } else {
            // Use API polling for other active statuses
            this.startApiPolling();
        }
    }

    /**
     * Start Firebase real-time tracking with ride completion detection
     */
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

            // Listen for ride status changes to stop tracking when completed
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

    /**
     * Start API polling as fallback
     */
    startApiPolling() {
        this.trackingInterval = setInterval(() => {
            this.fetchDriverLocation();
        }, 5000); // Update every 5 seconds
    }

    /**
     * Fetch driver location from API with ride completion detection
     */
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

            // Check if ride status changed to completed and stop tracking
            if (data.status && (data.status === 'completed' || data.status === 'finished')) {
                console.log('Ride completed, stopping tracking');
                this.stopTracking();
            }
        } catch (error) {
            console.error('Error fetching driver location:', error);
        }
    }

    /**
     * Update driver position with smooth animation
     */
    updateDriverPosition(newPosition) {
        if (!this.driverMarker) return;

        const currentPosition = this.driverMarker.getPosition();

        if (currentPosition) {
            // Animate marker movement
            this.animateMarker(this.driverMarker, currentPosition, newPosition);
        } else {
            // First position update
            this.driverMarker.setPosition(newPosition);
        }

        // Update map center if driver is far from view
        const bounds = this.map.getBounds();
        if (bounds && !bounds.contains(newPosition)) {
            this.map.panTo(newPosition);
        }
    }

    /**
     * Animate marker movement
     */
    animateMarker(marker, startPos, endPos) {
        const startLat = startPos.lat();
        const startLng = startPos.lng();
        const endLat = endPos.lat;
        const endLng = endPos.lng;

        let step = 0;
        const numSteps = 50;
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

    /**
     * Show message when no drop-off location is set
     */
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

    /**
     * Stop all tracking activities
     */
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

    /**
     * Cleanup resources
     */
    cleanup() {
        this.stopTracking();
    }

    /**
     * Refresh ride data
     */
    async refreshRideData() {
        try {
            const response = await fetch(`/api/rides/${this.rideData.id}/tracking-data`);
            const data = await response.json();

            if (data.ride) {
                this.rideData = {
                    ...this.rideData,
                    status: data.ride.status,
                    routePoints: data.route_points
                };

                // Re-handle status if it changed
                this.handleRideStatus();
            }
        } catch (error) {
            console.error('Error refreshing ride data:', error);
        }
    }
}

// Export for use in Blade templates
window.RideTracker = RideTracker;