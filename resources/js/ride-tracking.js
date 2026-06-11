/**
 * Real-time ride tracking — uses Laravel Echo (Reverb WebSocket).
 * Firebase references have been fully removed.
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
        this.liveDriverLine = null;
        this.trackingInterval = null;
        this.echoChannel = null;
        this.echoStatusChannel = null;
    }

    /** Initialize the map and tracking */
    init() {
        if (!this.rideData.pickup.lat || !this.rideData.pickup.lng) {
            console.log('No pickup coordinates available');
            return;
        }

        this.initMap();
        this.setupMarkers();
        this.handleRideStatus();
    }

    /** Initialize Google Maps */
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

    /** Setup map markers */
    setupMarkers() {
        this.pickupMarker = new google.maps.Marker({
            position: { lat: this.rideData.pickup.lat, lng: this.rideData.pickup.lng },
            map: this.map,
            title: 'Pickup Location',
            icon: this.createMarkerIcon('#4CAF50', 'P')
        });

        const pickupInfoWindow = new google.maps.InfoWindow({
            content: `<div><strong>Pickup Location</strong><br>${this.rideData.pickup.address || 'Pickup Point'}</div>`
        });
        this.pickupMarker.addListener('click', () => {
            pickupInfoWindow.open(this.map, this.pickupMarker);
        });

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
            this.showNoDropoffMessage();
        }
    }

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

    async showCompletedRoute() {
        try {
            const response = await fetch(`/api/rides/${this.rideData.id}/route-points`);
            const data = await response.json();

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

                const bounds = new google.maps.LatLngBounds();
                routePath.forEach(point => bounds.extend(point));
                if (this.pickupMarker) bounds.extend(this.pickupMarker.getPosition());
                if (this.dropoffMarker) bounds.extend(this.dropoffMarker.getPosition());
                this.map.fitBounds(bounds);
            } else {
                this.showStaticRoute();
            }
        } catch (error) {
            console.error('Error fetching route points:', error);
            this.showStaticRoute();
        }
    }

    showLiveTracking() {
        if (this.rideData.dropoff.lat && this.rideData.dropoff.lng) {
            const request = {
                origin: { lat: this.rideData.pickup.lat, lng: this.rideData.pickup.lng },
                destination: { lat: this.rideData.dropoff.lat, lng: this.rideData.dropoff.lng },
                travelMode: google.maps.TravelMode.DRIVING,
            };
            this.directionsService.route(request, (result, status) => {
                if (status === 'OK') this.directionsRenderer.setDirections(result);
            });
        }

        this.driverMarker = new google.maps.Marker({
            map: this.map,
            title: 'Driver Location',
            icon: this.createDriverIcon(),
            zIndex: 1000
        });

        this.startRealTimeTracking();
    }

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

    startRealTimeTracking() {
        this.startEchoTracking();
        // API polling as safety net
        this.startApiPolling();
    }

    /**
     * Subscribe to the PUBLIC `driver-location` channel via Laravel Echo (Reverb).
     * Filter by driver_id client-side. Also watch `ride-updates` for status changes.
     */
    startEchoTracking() {
        if (typeof window.Echo === 'undefined') {
            console.warn('Laravel Echo not available — relying on API polling only');
            return;
        }

        const driverId = this.rideData.driverId;
        if (!driverId) {
            console.warn('No driver assigned, skipping Echo subscription');
            return;
        }

        console.log(`📡 Subscribing to driver-location for driver #${driverId}`);

        this.echoChannel = window.Echo.channel('driver-location')
            .listen('.driver.location.updated', (e) => {
                if (parseInt(e.driver_id) !== parseInt(driverId)) return;
                if (e.latitude && e.longitude && this.driverMarker) {
                    this.updateDriverPosition({
                        lat: parseFloat(e.latitude),
                        lng: parseFloat(e.longitude),
                        bearing: e.bearing
                    });
                }
            });

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
    }

    startApiPolling() {
        this.trackingInterval = setInterval(() => {
            this.fetchDriverLocation();
        }, 5000);
    }

    async fetchDriverLocation() {
        try {
            const response = await fetch(`/api/rides/${this.rideData.id}/tracking-data`);
            const data = await response.json();

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

        if (this.liveDriverLine) this.liveDriverLine.setMap(this.map);
    }

    updateDriverPosition(newPosition) {
        if (!this.driverMarker) return;

        const currentPosition = this.driverMarker.getPosition();
        if (currentPosition) {
            this.animateMarker(this.driverMarker, currentPosition, newPosition);
        } else {
            this.driverMarker.setPosition(newPosition);
        }

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
        const numSteps = 50;
        const timePerStep = 100;
        const stepLat = (endLat - startLat) / numSteps;
        const stepLng = (endLng - startLng) / numSteps;

        const animate = () => {
            if (step <= numSteps) {
                marker.setPosition({
                    lat: startLat + (stepLat * step),
                    lng: startLng + (stepLng * step)
                });
                step++;
                setTimeout(animate, timePerStep);
            }
        };
        animate();
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

    stopTracking() {
        if (this.echoChannel && typeof window.Echo !== 'undefined') {
            try { window.Echo.leaveChannel('driver-location'); } catch (e) { /* ignore */ }
            this.echoChannel = null;
        }
        if (this.echoStatusChannel && typeof window.Echo !== 'undefined') {
            try { window.Echo.leaveChannel('ride-updates'); } catch (e) { /* ignore */ }
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

    cleanup() {
        this.stopTracking();
    }

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
                this.handleRideStatus();
            }
        } catch (error) {
            console.error('Error refreshing ride data:', error);
        }
    }
}

// Export for use in Blade templates
window.RideTracker = RideTracker;