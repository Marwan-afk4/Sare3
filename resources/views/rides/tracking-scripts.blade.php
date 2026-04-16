<script>
/**
 * Inline Ride Tracking JavaScript (dashboard).
 *
 * In addition to pickup/dropoff and the full trip polyline, this tracker
 * renders:
 *   - An "A" marker at the captain's location when they accepted the ride
 *   - A dashed orange polyline for the "on the way to passenger" leg
 *   - A solid green polyline for the actual trip leg
 *   - A live-updating driver marker driven by Firebase Realtime Database
 *     and (fallback) the REST tracking-data endpoint. It works while the
 *     ride is in any of: accepted, waiting_user, in_progress.
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
        this.acceptMarker = null;
        this.arrivedMarker = null;
        this.toPickupPolyline = null;
        this.tripPolyline = null;
        this.trackingInterval = null;
        this.firebaseRideLocationRef = null;
        this.firebaseDriverRef = null;
        this.firebaseStatusRef = null;
        this.liveTrackingStatuses = ['accepted', 'waiting_user', 'in_progress'];
    }

    init() {
        console.log('Initializing ride tracker with data:', this.rideData);

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

        try {
            this.initMap();
            this.setupMarkers();
            this.handleRideStatus();

            // Pull the enriched tracking payload (segmented polylines,
            // accept/arrived markers, live driver location) once on init.
            this.fetchTrackingData();
        } catch (error) {
            console.error('Error initializing map:', error);
            document.getElementById(this.mapElementId).innerHTML = '<div class="alert alert-danger m-3"><strong>Error:</strong> Failed to initialize map. ' + error.message + '</div>';
        }
    }

    initMap() {
        const mapContainer = document.getElementById(this.mapElementId);
        if (!mapContainer) {
            console.error('Map container not found:', this.mapElementId);
            return;
        }

        mapContainer.innerHTML = '';
        if (mapContainer.offsetHeight === 0) {
            mapContainer.style.height = '400px';
        }

        this.map = new google.maps.Map(mapContainer, {
            zoom: 13,
            center: { lat: this.rideData.pickup.lat, lng: this.rideData.pickup.lng },
            mapTypeId: google.maps.MapTypeId.ROADMAP
        });

        this.directionsService = new google.maps.DirectionsService();
        this.directionsRenderer = new google.maps.DirectionsRenderer({
            suppressMarkers: true,
            polylineOptions: {
                strokeColor: '#4285F4',
                strokeWeight: 4
            }
        });
        this.directionsRenderer.setMap(this.map);

        setTimeout(() => {
            google.maps.event.trigger(this.map, 'resize');
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
            label: { text: 'P', color: 'white', fontWeight: 'bold' }
        });

        const pickupInfoWindow = new google.maps.InfoWindow({
            content: `<div><strong>Pickup Location</strong><br>${this.rideData.pickup.address || 'Pickup Point'}</div>`
        });
        this.pickupMarker.addListener('click', () => pickupInfoWindow.open(this.map, this.pickupMarker));

        // Dropoff marker
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
                label: { text: 'D', color: 'white', fontWeight: 'bold' }
            });

            const dropoffInfoWindow = new google.maps.InfoWindow({
                content: `<div><strong>Drop-off Location</strong><br>${this.rideData.dropoff.address || 'Destination'}</div>`
            });
            this.dropoffMarker.addListener('click', () => dropoffInfoWindow.open(this.map, this.dropoffMarker));
        } else {
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
        // Fallback: if we don't yet have segmented data from the API, draw
        // whatever flat route_points the Blade view already provided.
        if (this.rideData.routePoints && this.rideData.routePoints.length > 0) {
            const routePath = this.rideData.routePoints.map(point => ({
                lat: parseFloat(point.lat),
                lng: parseFloat(point.lng)
            }));

            this.tripPolyline = new google.maps.Polyline({
                path: routePath,
                geodesic: true,
                strokeColor: '#4CAF50',
                strokeOpacity: 1.0,
                strokeWeight: 4
            });
            this.tripPolyline.setMap(this.map);

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
                if (status === 'OK') this.directionsRenderer.setDirections(result);
            });
        }

        this.driverMarker = new google.maps.Marker({
            map: this.map,
            title: 'Driver Location (Live)',
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
                if (status === 'OK') this.directionsRenderer.setDirections(result);
            });
        }
    }

    /**
     * Pull segmented route, accept/arrived positions and the live driver
     * location from the backend in one shot, then render them on the map.
     */
    async fetchTrackingData() {
        try {
            const resp = await fetch(`/api/rides/${this.rideData.id}/tracking-data`);
            if (!resp.ok) return;
            const data = await resp.json();

            this.renderAcceptMarker(data.driver_accept_location);
            this.renderArrivedMarker(data.driver_arrived_location);
            this.renderToPickupPolyline(data.to_pickup_route_points || []);
            this.renderTripPolyline(data.trip_route_points || []);

            // Prime the live marker so the dashboard doesn't stay empty while
            // waiting for the next Firebase update or API poll.
            if (data.live_driver_location && this.driverMarker) {
                this.updateDriverPosition({
                    lat: parseFloat(data.live_driver_location.lat),
                    lng: parseFloat(data.live_driver_location.lng)
                });
            }

            this.fitBoundsToEverything(data);
        } catch (err) {
            console.warn('Failed to load enriched tracking data:', err);
        }
    }

    renderAcceptMarker(accept) {
        if (!accept || accept.lat == null || accept.lng == null) return;
        if (this.acceptMarker) this.acceptMarker.setMap(null);

        this.acceptMarker = new google.maps.Marker({
            position: { lat: parseFloat(accept.lat), lng: parseFloat(accept.lng) },
            map: this.map,
            title: 'Driver Accept Location',
            icon: {
                path: google.maps.SymbolPath.CIRCLE,
                scale: 10,
                fillColor: '#FF9800',
                fillOpacity: 1,
                strokeColor: 'white',
                strokeWeight: 2
            },
            label: { text: 'A', color: 'white', fontWeight: 'bold' },
            zIndex: 900
        });

        const when = accept.recorded_at
            ? new Date(accept.recorded_at).toLocaleString()
            : '';
        const info = new google.maps.InfoWindow({
            content: `<div><strong>Captain accepted here</strong><br><small>${when}</small></div>`
        });
        this.acceptMarker.addListener('click', () => info.open(this.map, this.acceptMarker));
    }

    renderArrivedMarker(arrived) {
        if (!arrived || arrived.lat == null || arrived.lng == null) return;
        if (this.arrivedMarker) this.arrivedMarker.setMap(null);

        this.arrivedMarker = new google.maps.Marker({
            position: { lat: parseFloat(arrived.lat), lng: parseFloat(arrived.lng) },
            map: this.map,
            title: 'Driver Arrived at Pickup',
            icon: {
                path: google.maps.SymbolPath.CIRCLE,
                scale: 8,
                fillColor: '#9C27B0',
                fillOpacity: 1,
                strokeColor: 'white',
                strokeWeight: 2
            },
            zIndex: 900
        });
    }

    renderToPickupPolyline(points) {
        if (!points || points.length < 2) return;
        if (this.toPickupPolyline) this.toPickupPolyline.setMap(null);

        const path = points.map(p => ({
            lat: parseFloat(p.lat),
            lng: parseFloat(p.lng)
        }));

        this.toPickupPolyline = new google.maps.Polyline({
            path,
            geodesic: true,
            strokeColor: '#FF9800',
            strokeOpacity: 0,
            strokeWeight: 4,
            icons: [{
                icon: {
                    path: 'M 0,-1 0,1',
                    strokeOpacity: 1,
                    strokeColor: '#FF9800',
                    scale: 3
                },
                offset: '0',
                repeat: '12px'
            }]
        });
        this.toPickupPolyline.setMap(this.map);
    }

    renderTripPolyline(points) {
        if (!points || points.length < 2) return;
        if (this.tripPolyline) this.tripPolyline.setMap(null);

        const path = points.map(p => ({
            lat: parseFloat(p.lat),
            lng: parseFloat(p.lng)
        }));

        this.tripPolyline = new google.maps.Polyline({
            path,
            geodesic: true,
            strokeColor: '#4CAF50',
            strokeOpacity: 1.0,
            strokeWeight: 4
        });
        this.tripPolyline.setMap(this.map);
    }

    fitBoundsToEverything(data) {
        try {
            const bounds = new google.maps.LatLngBounds();
            let has = false;

            if (this.rideData.pickup.lat) {
                bounds.extend({ lat: this.rideData.pickup.lat, lng: this.rideData.pickup.lng });
                has = true;
            }
            if (this.rideData.dropoff.lat) {
                bounds.extend({ lat: this.rideData.dropoff.lat, lng: this.rideData.dropoff.lng });
                has = true;
            }
            if (data.driver_accept_location) {
                bounds.extend({
                    lat: parseFloat(data.driver_accept_location.lat),
                    lng: parseFloat(data.driver_accept_location.lng)
                });
                has = true;
            }
            (data.to_pickup_route_points || []).forEach(p => {
                bounds.extend({ lat: parseFloat(p.lat), lng: parseFloat(p.lng) });
                has = true;
            });
            (data.trip_route_points || []).forEach(p => {
                bounds.extend({ lat: parseFloat(p.lat), lng: parseFloat(p.lng) });
                has = true;
            });
            if (data.live_driver_location) {
                bounds.extend({
                    lat: parseFloat(data.live_driver_location.lat),
                    lng: parseFloat(data.live_driver_location.lng)
                });
                has = true;
            }

            if (has) this.map.fitBounds(bounds);
        } catch (e) {
            console.warn('fitBoundsToEverything failed:', e);
        }
    }

    startTracking() {
        if (this.liveTrackingStatuses.includes(this.rideData.status)) {
            this.startFirebaseTracking();
        }
        // Always keep an API polling safety net so the dashboard still works
        // if Firebase is unreachable or the driver only writes to the DB.
        this.startApiPolling();
    }

    startFirebaseTracking() {
        try {
            if (typeof firebase === 'undefined' || !firebase.database) {
                console.log('Firebase not available, relying on API polling');
                return;
            }

            const firebaseRideId = this.rideData.firebaseRideId || `ride_${this.rideData.id}`;

            // Primary source: per-ride driver_location node written by the
            // mobile app / updateDriverLocation endpoint.
            this.firebaseRideLocationRef = firebase.database().ref(`rides/${firebaseRideId}/driver_location`);
            this.firebaseRideLocationRef.on('value', (snapshot) => {
                const location = snapshot.val();
                if (location && location.lat && location.lng && this.driverMarker) {
                    this.updateDriverPosition({
                        lat: parseFloat(location.lat),
                        lng: parseFloat(location.lng)
                    });
                }
            });

            // Secondary source: driver's global node (drivers/{id}). This is
            // the same feed the mobile driver app writes while online, so we
            // keep getting updates even if the per-ride node lags.
            if (this.rideData.driverId) {
                this.firebaseDriverRef = firebase.database().ref(`drivers/${this.rideData.driverId}`);
                this.firebaseDriverRef.on('value', (snapshot) => {
                    const loc = snapshot.val();
                    if (loc && loc.latitude && loc.longitude && this.driverMarker) {
                        this.updateDriverPosition({
                            lat: parseFloat(loc.latitude),
                            lng: parseFloat(loc.longitude)
                        });
                    }
                });
            }

            this.firebaseStatusRef = firebase.database().ref(`rides/${firebaseRideId}/status`);
            this.firebaseStatusRef.on('value', (snapshot) => {
                const status = snapshot.val();
                if (status && (status === 'completed' || status === 'finished' || status === 'finshed')) {
                    this.stopTracking();
                }
            });
        } catch (error) {
            console.error('Firebase tracking error:', error);
        }
    }

    startApiPolling() {
        if (this.trackingInterval) return;
        this.trackingInterval = setInterval(() => {
            this.fetchDriverLocation();
        }, 5000);
    }

    async fetchDriverLocation() {
        try {
            const response = await fetch(`/api/rides/${this.rideData.id}/tracking-data`);
            if (!response.ok) return;
            const data = await response.json();

            // Redraw the segmented polylines; they grow while the captain
            // is driving so the admin sees the path update over time.
            this.renderToPickupPolyline(data.to_pickup_route_points || []);
            this.renderTripPolyline(data.trip_route_points || []);

            if (data.live_driver_location && this.driverMarker) {
                this.updateDriverPosition({
                    lat: parseFloat(data.live_driver_location.lat),
                    lng: parseFloat(data.live_driver_location.lng)
                });
            } else if (data.latest_location && this.driverMarker) {
                this.updateDriverPosition({
                    lat: parseFloat(data.latest_location.lat),
                    lng: parseFloat(data.latest_location.lng)
                });
            }

            const status = data.ride?.status;
            if (status && (status === 'completed' || status === 'finished' || status === 'finshed')) {
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
            this.animateMarker(this.driverMarker, currentPosition, newPosition);
        } else {
            this.driverMarker.setPosition(newPosition);
        }

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
        if (this.firebaseRideLocationRef) {
            this.firebaseRideLocationRef.off();
            this.firebaseRideLocationRef = null;
        }
        if (this.firebaseDriverRef) {
            this.firebaseDriverRef.off();
            this.firebaseDriverRef = null;
        }
        if (this.firebaseStatusRef) {
            this.firebaseStatusRef.off();
            this.firebaseStatusRef = null;
        }
        if (this.trackingInterval) {
            clearInterval(this.trackingInterval);
            this.trackingInterval = null;
        }
    }

    showNoDropoffMessage() {
        const noDropoffInfoWindow = new google.maps.InfoWindow({
            content: `<div class="alert alert-warning mb-0">
                        <strong>No Drop-off Location</strong><br>
                        No drop-off location has been selected for this ride.
                      </div>`,
            position: { lat: this.rideData.pickup.lat, lng: this.rideData.pickup.lng }
        });
        noDropoffInfoWindow.open(this.map);
    }

    cleanup() {
        this.stopTracking();
    }

    refreshRideData() {
        // Soft refresh: just re-pull the tracking data without reloading
        // the whole page, so the admin doesn't lose their map interaction.
        this.fetchTrackingData();
    }
}

window.SimpleRideTracker = SimpleRideTracker;
</script>
