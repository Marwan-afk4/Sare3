<script>
/**
 * Inline Ride Tracking JavaScript (admin dashboard).
 *
 * Uses Laravel Echo → Reverb WebSocket for real-time updates:
 *   - Public channel `driver-location`  → '.driver.location.updated'
 *   - Public channel `ride-updates`     → '.ride.status.updated'
 *
 * Renders on the map:
 *   - Orange "A" marker at the captain's accept location
 *   - Dashed orange polyline: captain → passenger (to_pickup leg)
 *   - Solid green polyline: passenger → destination (trip leg)
 *   - Live blue driver marker, updated via WebSocket in real time
 *   - Live dashed/solid line showing current driver movement
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
        this.liveDriverLine = null;   // live segment: driver→pickup or pickup→driver
        this.trackingInterval = null;
        this.echoChannel = null;
        this.echoStatusChannel = null;
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

            // Pull the enriched tracking payload once on init.
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
                strokeWeight: 4,
                strokeOpacity: 0.5
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
            label: { text: 'P', color: 'white', fontWeight: 'bold', fontSize: '11px' },
            zIndex: 800
        });

        // Dropoff marker (if available)
        if (this.rideData.dropoff.lat && this.rideData.dropoff.lng) {
            this.dropoffMarker = new google.maps.Marker({
                position: { lat: this.rideData.dropoff.lat, lng: this.rideData.dropoff.lng },
                map: this.map,
                title: 'Drop-off Location',
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: 10,
                    fillColor: '#F44336',
                    fillOpacity: 1,
                    strokeColor: 'white',
                    strokeWeight: 2
                },
                label: { text: 'D', color: 'white', fontWeight: 'bold', fontSize: '11px' },
                zIndex: 800
            });
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
        // Draw the planned pickup→dropoff route as a faint guide
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

        // Create the driver live-position marker (no position yet — will be set
        // from WebSocket or the API poll).
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
            // waiting for the next WebSocket update or API poll.
            if (data.live_driver_location && this.driverMarker) {
                this.updateDriverPosition({
                    lat: parseFloat(data.live_driver_location.lat),
                    lng: parseFloat(data.live_driver_location.lng),
                    bearing: data.live_driver_location.bearing
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

    /**
     * Draw (or redraw) a live line between the driver's current position
     * and the relevant waypoint:
     *  - accepted / waiting_user: dashed orange  driver → pickup
     *  - in_progress:             solid blue     pickup → driver
     */
    updateLiveDriverLine(driverPos) {
        if (!driverPos || !driverPos.lat || !driverPos.lng) return;
        const status = this.rideData.status;
        const pickup = this.rideData.pickup;
        if (!pickup || !pickup.lat) return;

        if (this.liveDriverLine) {
            this.liveDriverLine.setMap(null);
            this.liveDriverLine = null;
        }

        if (status === 'accepted' || status === 'waiting_user') {
            this.liveDriverLine = new google.maps.Polyline({
                path: [
                    { lat: parseFloat(driverPos.lat), lng: parseFloat(driverPos.lng) },
                    { lat: parseFloat(pickup.lat), lng: parseFloat(pickup.lng) }
                ],
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
        } else if (status === 'in_progress') {
            this.liveDriverLine = new google.maps.Polyline({
                path: [
                    { lat: parseFloat(pickup.lat), lng: parseFloat(pickup.lng) },
                    { lat: parseFloat(driverPos.lat), lng: parseFloat(driverPos.lng) }
                ],
                geodesic: true,
                strokeColor: '#2196F3',
                strokeOpacity: 0.9,
                strokeWeight: 4
            });
        }

        if (this.liveDriverLine) {
            this.liveDriverLine.setMap(this.map);
        }
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

    // ─── Tracking start ───────────────────────────────────────────────────────

    startTracking() {
        if (this.liveTrackingStatuses.includes(this.rideData.status)) {
            this.startEchoTracking();
        }
        // Always keep an API polling safety net
        this.startApiPolling();
    }

    /**
     * Subscribe to the PUBLIC `driver-location` channel via Laravel Echo.
     * We filter events client-side by driver_id so we only react to this ride's driver.
     * Also subscribe to `ride-updates` to detect status changes.
     */
    startEchoTracking() {
        if (typeof window.Echo === 'undefined') {
            console.warn('Laravel Echo not available, relying on API polling only');
            return;
        }

        const driverId = this.rideData.driverId;
        if (!driverId) {
            console.warn('No driver assigned yet, skipping Echo subscription');
            return;
        }

        console.log(`📡 Subscribing to driver-location for driver #${driverId}`);

        // Listen on the PUBLIC driver-location channel (no auth needed for admin)
        this.echoChannel = window.Echo.channel('driver-location')
            .listen('.driver.location.updated', (e) => {
                // Filter: only react to our ride's driver
                if (parseInt(e.driver_id) !== parseInt(driverId)) return;

                console.log('📍 Driver location update via WebSocket:', e);

                if (e.latitude && e.longitude && this.driverMarker) {
                    this.updateDriverPosition({
                        lat: parseFloat(e.latitude),
                        lng: parseFloat(e.longitude),
                        bearing: e.bearing
                    });
                }
            });

        // Listen for ride status changes on the PUBLIC ride-updates channel
        this.echoStatusChannel = window.Echo.channel('ride-updates')
            .listen('.ride.status.updated', (e) => {
                if (parseInt(e.ride_id) !== parseInt(this.rideData.id)) return;
                console.log('🔄 Ride status changed via WebSocket:', e.status);

                this.updateStatusUI(e.status);

                // Redraw line immediately if we have driver location
                if (this.driverMarker && this.driverMarker.getPosition()) {
                    const pos = this.driverMarker.getPosition();
                    this.updateLiveDriverLine({ lat: pos.lat(), lng: pos.lng() });
                }

                if (['completed', 'finished', 'finshed'].includes(e.status)) {
                    this.stopTracking();
                    this.showCompletedRoute();
                }
            });

        // Bind connection state to the UI badge
        try {
            window.Echo.connector.pusher.connection.bind('connected', () => {
                this._updateConnectionBadge('connected', '🟢 Live');
            });
            window.Echo.connector.pusher.connection.bind('disconnected', () => {
                this._updateConnectionBadge('disconnected', '🔴 Disconnected');
            });
            window.Echo.connector.pusher.connection.bind('connecting', () => {
                this._updateConnectionBadge('connecting', '⏳ Connecting...');
            });
            // Reflect initial state immediately
            const state = window.Echo.connector.pusher.connection.state;
            if (state === 'connected') {
                this._updateConnectionBadge('connected', '🟢 Live');
            }
        } catch (e) {
            console.warn('Could not bind Echo connection events:', e);
        }
    }

    _updateConnectionBadge(status, message) {
        const el = document.getElementById('connection-status');
        if (!el) return;
        el.className = `badge ${
            status === 'connected'    ? 'bg-success' :
            status === 'connecting'   ? 'bg-warning text-dark' :
                                        'bg-danger'
        }`;
        el.textContent = message;
    }

    updateStatusUI(status) {
        this.rideData.status = status;

        // Update the status indicator class
        const indicatorEl = document.getElementById('ride-status-indicator');
        if (indicatorEl) {
            let indicatorClass = 'pending';
            if (['completed', 'finished', 'finshed'].includes(status)) {
                indicatorClass = 'completed';
            } else if (['in_progress', 'accepted', 'waiting_user'].includes(status)) {
                indicatorClass = 'live';
            }
            indicatorEl.className = `status-indicator status-${indicatorClass}`;
        }

        // Update status text
        const textEl = document.getElementById('ride-status-text');
        if (textEl) {
            if (status === 'in_progress') {
                textEl.innerHTML = `<i class="fa fa-broadcast-tower text-success"></i> ${this.translate('Real-time WebSocket Tracking Active')}`;
            } else if (['accepted', 'waiting_user'].includes(status)) {
                textEl.innerHTML = `<i class="fa fa-location-arrow text-info"></i> ${this.translate('Live Tracking Active')}`;
            } else if (['completed', 'finished', 'finshed'].includes(status)) {
                textEl.innerHTML = `<i class="fa fa-check-circle text-success"></i> ${this.translate('Trip Completed')}`;
            } else {
                const label = this.getStatusLabel(status);
                textEl.innerHTML = `<i class="fa fa-clock text-warning"></i> ${label}`;
            }
        }

        // Update status badge
        const badgeEl = document.getElementById('ride-status-badge');
        if (badgeEl) {
            const color = this.getStatusColor(status);
            const textColor = this.getStatusTextColor(status);
            const label = this.getStatusLabel(status);
            badgeEl.innerHTML = `<span class="badge rounded-pill px-3 py-2" style="background-color: #${color}; color: #${textColor};">${label}</span>`;
        }
    }

    translate(key) {
        const isAr = document.documentElement.lang === 'ar' || document.dir === 'rtl';
        if (isAr) {
            if (key === 'Real-time WebSocket Tracking Active') return 'تتبع WebSocket اللحظي مفعل';
            if (key === 'Live Tracking Active') return 'التتبع المباشر نشط';
            if (key === 'Trip Completed') return 'اكتملت الرحلة';
        }
        return key;
    }

    getStatusLabel(status) {
        const isAr = document.documentElement.lang === 'ar' || document.dir === 'rtl';
        const labels = {
            'pending': isAr ? 'معلق' : 'Pending',
            'in_progress': isAr ? 'قيد التنفيذ' : 'In Progress',
            'completed': isAr ? 'مكتملة' : 'Completed',
            'cancelled': isAr ? 'ملغية' : 'Cancelled',
            'arrived': isAr ? 'وصل الكابتن' : 'Arrived',
            'waiting_user': isAr ? 'في انتظار العميل' : 'Waiting User',
            'rejected': isAr ? 'مرفوضة' : 'Rejected',
            'accepted': isAr ? 'مقبولة' : 'Accepted',
            'finshed': isAr ? 'مكتملة' : 'Finished',
            'finished': isAr ? 'مكتملة' : 'Finished'
        };
        return labels[status] || status;
    }

    getStatusColor(status) {
        const colors = {
            'pending': 'FBBF24',
            'in_progress': '3B82F6',
            'completed': '22C55E',
            'cancelled': 'EF4444',
            'arrived': 'FBBF24',
            'waiting_user': 'FBBF24',
            'rejected': 'EF4444',
            'accepted': '22C55E',
            'finshed': '22C55E',
            'finished': '22C55E'
        };
        return colors[status] || 'FBBF24';
    }

    getStatusTextColor(status) {
        const textColors = {
            'pending': '000000',
            'in_progress': 'FFFFFF',
            'completed': 'FFFFFF',
            'cancelled': 'FFFFFF',
            'arrived': '000000',
            'waiting_user': '000000',
            'rejected': 'FFFFFF',
            'accepted': 'FFFFFF',
            'finshed': 'FFFFFF',
            'finished': 'FFFFFF'
        };
        return textColors[status] || 'FFFFFF';
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

            // Redraw the segmented polylines — they grow while the captain is driving
            this.renderToPickupPolyline(data.to_pickup_route_points || []);
            this.renderTripPolyline(data.trip_route_points || []);

            if (data.live_driver_location && this.driverMarker) {
                this.updateDriverPosition({
                    lat: parseFloat(data.live_driver_location.lat),
                    lng: parseFloat(data.live_driver_location.lng),
                    bearing: data.live_driver_location.bearing
                });
            } else if (data.latest_location && this.driverMarker) {
                this.updateDriverPosition({
                    lat: parseFloat(data.latest_location.lat),
                    lng: parseFloat(data.latest_location.lng)
                });
            }

            const status = data.ride?.status;
            if (status) {
                this.updateStatusUI(status);
                if (['completed', 'finished', 'finshed'].includes(status)) {
                    this.stopTracking();
                    this.showCompletedRoute();
                }
            }
        } catch (error) {
            console.error('Error fetching driver location:', error);
        }
    }

    updateDriverPosition(newPosition) {
        if (!this.driverMarker) return;

        // Update arrow bearing/rotation if available
        if (newPosition.bearing != null) {
            const icon = this.driverMarker.getIcon();
            if (icon) {
                icon.rotation = newPosition.bearing;
                this.driverMarker.setIcon(icon);
            }
        }

        const currentPosition = this.driverMarker.getPosition();
        if (currentPosition) {
            this.animateMarker(this.driverMarker, currentPosition, newPosition);
        } else {
            this.driverMarker.setPosition(newPosition);
        }

        // Redraw the live tracking line on every position update
        this.updateLiveDriverLine(newPosition);

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
        // Unsubscribe from Echo channels
        if (this.echoChannel && typeof window.Echo !== 'undefined') {
            try {
                window.Echo.leaveChannel('driver-location');
            } catch (e) { /* ignore */ }
            this.echoChannel = null;
        }
        if (this.echoStatusChannel && typeof window.Echo !== 'undefined') {
            try {
                window.Echo.leaveChannel('ride-updates');
            } catch (e) { /* ignore */ }
            this.echoStatusChannel = null;
        }

        if (this.trackingInterval) {
            clearInterval(this.trackingInterval);
            this.trackingInterval = null;
        }
        if (this.liveDriverLine) {
            this.liveDriverLine.setMap(null);
            this.liveDriverLine = null;
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
        this.fetchTrackingData();
    }
}

window.SimpleRideTracker = SimpleRideTracker;
</script>
