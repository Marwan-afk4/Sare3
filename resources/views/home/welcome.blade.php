@extends('layouts.app')

@php
    $currentPage = 'home';
@endphp

@section('title', 'الصفحه الرئيسية')

@section('content')
    <div class="main-card mb-3 card">
        <div class="card-body">
            <div class="row gx-4 gy-6 pb-5">
                <div class="col-xxl-6">
                    <div class="mb-3">
                        <h3>{{ __('New Users & Drivers') }}</h3>
                        <p class="text-body-tertiary mb-0">{{ __('Number of new registered accounts') }}</p>
                    </div>
                    <div class="row g-6">
                        <div class="col-md-6 mb-2 mb-sm-0">
                            <div class="d-flex align-items-center">
                                <span class="me-2 text-info" data-feather="users" style="min-height:24px; width:24px"></span>
                                <h4 class="text-body-tertiary mb-0">
                                    {{ __('New Users') }} :
                                    <span class="text-body-emphasis"> {{ $userCount }} </span>
                                </h4>
                            </div>
                            <div class="pb-0 pt-4">
                                <div class="echarts-new-users" style="min-height:300px;width:100%;"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <span class="me-2 text-primary" data-feather="user-check"
                                    style="height:24px; width:24px"></span>
                                <h4 class="text-body-tertiary mb-0">
                                    {{ __('New Drivers') }} :
                                    <span class="text-body-emphasis"> {{ $driverCount }} </span>
                                </h4>
                            </div>
                            <div class="pb-0 pt-4">
                                <div class="echarts-new-drivers" style="min-height:300px;width:100%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Active Rides Widget -->
                <div class="col-xxl-6">
                    <x-active-rides-widget :activeRides="$activeRides" />
                </div>

            </div>
        </div>
    </div>

    <!-- Available Drivers Map Section -->
    <div class="main-card mb-3 card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0">{{ __('Available Drivers') }}</h4>
                    <p class="text-body-tertiary mb-0 mt-1">{{ __('Real-time location of online drivers') }}</p>
                </div>
                <div class="text-end">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-success" id="available-drivers-badge" style="font-size: 1.2rem; padding: 0.5rem 1rem;">
                            <i class="fa fa-circle text-success me-1" style="font-size: 0.5rem;"></i>
                            {{ $availableDriversCount }} / {{ $driverCount }}
                        </span>
                    </div>
                    <small class="text-muted">{{ __('Online Drivers') }}</small>
                </div>
            </div>
        </div>
        <div class="card-body">
            @if($availableDriversCount > 0)
                <div id="available-drivers-map" style="height: 500px; border-radius: 8px; border: 1px solid #e0e0e0;"></div>
                <div class="mt-3 text-center">
                    <small class="text-muted">
                        <i class="fa fa-info-circle"></i> {{ __('Map updates in real-time via Firebase') }}
                    </small>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fa fa-car text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-3">{{ __('No drivers are currently online') }}</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Unavailable Drivers Map Section -->
    <div class="main-card mb-3 card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0">{{ __('Unavailable Drivers') }}</h4>
                    <p class="text-body-tertiary mb-0 mt-1">{{ __('Real-time location of offline drivers') }}</p>
                </div>
                <div class="text-end">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-danger" id="unavailable-drivers-badge" style="font-size: 1.2rem; padding: 0.5rem 1rem;">
                            <i class="fa fa-circle text-danger me-1" style="font-size: 0.5rem;"></i>
                            {{ $unavailableDriversCount }} / {{ $driverCount }}
                        </span>
                    </div>
                    <small class="text-muted">{{ __('Offline Drivers') }}</small>
                </div>
            </div>
        </div>
        <div class="card-body">
            @if($unavailableDriversCount > 0)
                <div id="unavailable-drivers-map" style="height: 500px; border-radius: 8px; border: 1px solid #e0e0e0;"></div>
                <div class="mt-3 text-center">
                    <small class="text-muted">
                        <i class="fa fa-info-circle"></i> {{ __('Map updates in real-time via Firebase') }}
                    </small>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fa fa-car text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-3">{{ __('No unavailable drivers at the moment') }}</p>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />
<style>
    .driver-marker {
        background-color: #4CAF50;
        border: 3px solid white;
        border-radius: 50%;
        width: 16px;
        height: 16px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.3);
    }
    .driver-cluster {
        background-color: #4CAF50;
        color: white;
        border-radius: 50%;
        text-align: center;
        font-weight: bold;
    }
    .unavailable-driver-marker {
        background-color: #f44336;
        border: 3px solid white;
        border-radius: 50%;
        width: 16px;
        height: 16px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.3);
    }
    .unavailable-driver-cluster {
        background-color: #f44336;
        color: white;
        border-radius: 50%;
        text-align: center;
        font-weight: bold;
    }
</style>
@endpush

@push('scripts')
    <!-- Feather Icons -->
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <!-- ECharts -->
    <script src="https://cdn.jsdelivr.net/npm/echarts/dist/echarts.min.js"></script>
    <!-- Leaflet for maps -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
    <!-- Firebase SDK -->
    <script src="https://www.gstatic.com/firebasejs/9.0.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.0.0/firebase-database-compat.js"></script>

    @php
        $safeUserCounts = $userMonthlyCounts ?? [12, 15, 20, 18, 22, 30, 25, 28, 24, 26, 30, 33];
        $safeDriverCounts = $driverMonthlyCounts ?? [5, 7, 9, 6, 10, 12, 8, 11, 9, 13, 14, 16];
    @endphp

    <script>
        function renderLineChart(selector, seriesName, seriesData) {
            const months = [
                "January", "February", "March", "April", "May", "June",
                "July", "August", "September", "October", "November", "December"
            ];

            const el = document.querySelector(selector);
            if (!el) return;

            if (echarts.getInstanceByDom(el)) {
                echarts.getInstanceByDom(el).dispose();
            }

            // ✅ detect dark mode (لو فيه كلاس اسمه dark-mode في body)
            const isDark = localStorage.getItem('phoenixTheme') === 'dark';

            const chart = echarts.init(el, null, {
                backgroundColor: 'transparent'
            });

            chart.setOption({
                tooltip: {
                    trigger: 'axis',
                    backgroundColor: isDark ? '#333' : '#fff',
                    textStyle: {
                        color: isDark ? '#fff' : '#000'
                    },
                    borderWidth: 0,
                    formatter: function(params) {
                        return `
                        <div>
                            <h6 class="fs-9 mb-0" style="color:${isDark ? '#fff' : '#333'}">
                                <span class="fas fa-circle me-1" style='color:${params[0].color}'></span>
                                ${params[0].seriesName} : ${params[0].value}
                            </h6>
                        </div>
                    `;
                    }
                },
                xAxis: {
                    type: 'category',
                    data: months,
                    axisLabel: {
                        color: isDark ? '#ddd' : '#333', // ✅ ألوان الأرقام
                        formatter: value => value.substring(0, 3)
                    },
                    axisLine: {
                        lineStyle: {
                            color: isDark ? '#555' : '#ccc'
                        }
                    }
                },
                yAxis: {
                    type: 'value',
                    min: 0,
                    axisLabel: {
                        color: isDark ? '#ddd' : '#333' // ✅ ألوان الأرقام
                    },
                    splitLine: {
                        lineStyle: {
                            color: isDark ? '#444' : '#eee'
                        }
                    }
                },
                series: [{
                    name: seriesName,
                    type: 'line',
                    data: seriesData,
                    smooth: true,
                    symbol: 'circle',
                    symbolSize: 8,
                    lineStyle: {
                        width: 3,
                        color: isDark ? '#4dabf7' : '#1971c2'
                    },
                    itemStyle: {
                        borderWidth: 2,
                        color: isDark ? '#74c0fc' : '#228be6'
                    }
                }]
            });
        }

        function initHomePageScripts() {
            feather.replace();

            const userMonthlyCounts = @json($safeUserCounts);
            const driverMonthlyCounts = @json($safeDriverCounts);

            renderLineChart('.echarts-new-users', 'Users', userMonthlyCounts);
            renderLineChart('.echarts-new-drivers', 'Drivers', driverMonthlyCounts);
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initHomePageScripts);
        } else {
            initHomePageScripts();
        }

        document.addEventListener('turbo:load', initHomePageScripts);
        document.addEventListener('livewire:load', initHomePageScripts);

        // ✅ إعادة رسم الرسوم عند تغيير الثيم (Light/Dark)
        const observer = new MutationObserver(() => {
            initHomePageScripts();
        });

        observer.observe(document.body, {
            attributes: true,
            attributeFilter: ['class']
        });

        // ✅ دعم الـ event لو الثيم بيبعت إشارة
        // ✅ لما localStorage يتغير (مثلاً phoenixTheme يتبدل) على نفس الصفحة أو من صفحة تانية
        window.addEventListener('storage', function(e) {
            if (e.key === 'phoenixTheme') {
                initHomePageScripts();
            }
        });

        // ✅ لو عندك زرار بيغير الثيم في نفس الصفحة
        // اعمل بعد ما تغير localStorage.dispatchEvent(new Event('themeChanged'));
        window.addEventListener('themeChanged', () => {
            initHomePageScripts();
        });
    </script>

    <!-- Available Drivers Map Script -->
    @if($availableDriversCount > 0)
    <script>
    (function() {
        // Initialize Firebase
        const firebaseConfig = {
            databaseURL: 'https://sarea-adce3-default-rtdb.firebaseio.com'
        };
        
        let firebaseApp = null;
        if (typeof firebase !== 'undefined' && !firebase.apps.length) {
            firebaseApp = firebase.initializeApp(firebaseConfig);
        }

        // Initial drivers data from server
        const initialDrivers = @json($availableDrivers);
        
        // Initialize map
        const mapElement = document.getElementById('available-drivers-map');
        if (!mapElement) return;

        // Calculate center point from available drivers
        let centerLat = 24.7136; // Default: Riyadh
        let centerLng = 46.6753;
        
        if (Object.keys(initialDrivers).length > 0) {
            const drivers = Object.values(initialDrivers);
            centerLat = drivers.reduce((sum, d) => sum + d.latitude, 0) / drivers.length;
            centerLng = drivers.reduce((sum, d) => sum + d.longitude, 0) / drivers.length;
        }

        const map = L.map('available-drivers-map').setView([centerLat, centerLng], 12);
        
        // Add tile layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19,
        }).addTo(map);

        // Create marker cluster group
        const markers = L.markerClusterGroup({
            iconCreateFunction: function(cluster) {
                const count = cluster.getChildCount();
                return L.divIcon({
                    html: '<div style="background-color: #4CAF50; color: white; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border: 3px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3); font-weight: bold;">' + count + '</div>',
                    className: 'driver-cluster',
                    iconSize: L.point(40, 40)
                });
            },
            spiderfyOnMaxZoom: true,
            showCoverageOnHover: false,
            zoomToBoundsOnClick: true
        });

        // Custom driver icon
        const driverIcon = L.divIcon({
            className: 'driver-marker',
            html: '<div style="background-color: #4CAF50; width: 16px; height: 16px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3);"></div>',
            iconSize: [22, 22],
            iconAnchor: [11, 11]
        });

        // Store markers by driver ID
        const driverMarkers = {};

        // Function to add or update a driver marker
        function updateDriverMarker(driverId, driverData) {
            const lat = parseFloat(driverData.latitude);
            const lng = parseFloat(driverData.longitude);
            
            if (isNaN(lat) || isNaN(lng)) return;

            // If marker exists, update its position
            if (driverMarkers[driverId]) {
                driverMarkers[driverId].setLatLng([lat, lng]);
            } else {
                // Create new marker
                const marker = L.marker([lat, lng], {
                    icon: driverIcon,
                    title: `Driver #${driverId}`
                });
                
                marker.bindPopup(`
                    <div style="text-align: center; min-width: 120px;">
                        <strong>{{ __('Driver') }} #${driverId}</strong><br>
                        <span class="badge bg-success mt-1">{{ __('Online') }} 🟢</span><br>
                        <small class="text-muted">Lat: ${lat.toFixed(6)}, Lng: ${lng.toFixed(6)}</small>
                    </div>
                `);
                
                markers.addLayer(marker);
                driverMarkers[driverId] = marker;
            }
        }

        // Function to remove a driver marker
        function removeDriverMarker(driverId) {
            if (driverMarkers[driverId]) {
                markers.removeLayer(driverMarkers[driverId]);
                delete driverMarkers[driverId];
            }
        }

        // Add initial markers
        Object.entries(initialDrivers).forEach(([driverId, driverData]) => {
            updateDriverMarker(driverId, driverData);
        });

        // Add marker cluster to map
        map.addLayer(markers);

        // Set up Firebase real-time listeners
        if (firebaseApp && firebase.database) {
            const driversRef = firebase.database().ref('drivers');
            
            // Listen for new drivers
            driversRef.on('child_added', (snapshot) => {
                const driverId = snapshot.key;
                const driverData = snapshot.val();
                
                if (driverData && driverData.latitude && driverData.longitude) {
                    // Normalize driver ID
                    let normalizedId = driverId;
                    if (isNaN(driverId)) {
                        const match = driverId.match(/(\d+)/);
                        if (match) normalizedId = match[1];
                    }
                    
                    updateDriverMarker(normalizedId, driverData);
                    
                    // Update badge count
                    updateDriverCount();
                }
            });

            // Listen for driver updates
            driversRef.on('child_changed', (snapshot) => {
                const driverId = snapshot.key;
                const driverData = snapshot.val();
                
                if (driverData && driverData.latitude && driverData.longitude) {
                    let normalizedId = driverId;
                    if (isNaN(driverId)) {
                        const match = driverId.match(/(\d+)/);
                        if (match) normalizedId = match[1];
                    }
                    
                    updateDriverMarker(normalizedId, driverData);
                }
            });

            // Listen for driver removal (went offline)
            driversRef.on('child_removed', (snapshot) => {
                const driverId = snapshot.key;
                
                let normalizedId = driverId;
                if (isNaN(driverId)) {
                    const match = driverId.match(/(\d+)/);
                    if (match) normalizedId = match[1];
                }
                
                removeDriverMarker(normalizedId);
                
                // Update badge count
                updateDriverCount();
            });
        }

        // Function to update driver count badge
        function updateDriverCount() {
            const count = Object.keys(driverMarkers).length;
            const badge = document.getElementById('available-drivers-badge');
            if (badge) {
                badge.innerHTML = `<i class="fa fa-circle text-success me-1" style="font-size: 0.5rem;"></i>${count} / {{ $driverCount }}`;
            }
        }

        // Cleanup on page unload
        window.addEventListener('beforeunload', () => {
            if (firebase.database) {
                firebase.database().ref('drivers').off();
            }
        });
    })();
    </script>
    @endif

    <!-- Unavailable Drivers Map Script -->
    @if($unavailableDriversCount > 0)
    <script>
    (function() {
        // Use existing Firebase app instance
        let firebaseApp = null;
        if (typeof firebase !== 'undefined' && firebase.apps.length > 0) {
            firebaseApp = firebase.apps[0];
        }

        // Initial unavailable drivers data from server
        const initialUnavailableDrivers = @json($unavailableDrivers);
        
        // Initialize map
        const mapElement = document.getElementById('unavailable-drivers-map');
        if (!mapElement) return;

        // Calculate center point from unavailable drivers
        let centerLat = 24.7136; // Default: Riyadh
        let centerLng = 46.6753;
        
        if (Object.keys(initialUnavailableDrivers).length > 0) {
            const drivers = Object.values(initialUnavailableDrivers);
            centerLat = drivers.reduce((sum, d) => sum + d.latitude, 0) / drivers.length;
            centerLng = drivers.reduce((sum, d) => sum + d.longitude, 0) / drivers.length;
        }

        const map = L.map('unavailable-drivers-map').setView([centerLat, centerLng], 12);
        
        // Add tile layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19,
        }).addTo(map);

        // Create marker cluster group
        const markers = L.markerClusterGroup({
            iconCreateFunction: function(cluster) {
                const count = cluster.getChildCount();
                return L.divIcon({
                    html: '<div style="background-color: #f44336; color: white; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border: 3px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3); font-weight: bold;">' + count + '</div>',
                    className: 'unavailable-driver-cluster',
                    iconSize: L.point(40, 40)
                });
            },
            spiderfyOnMaxZoom: true,
            showCoverageOnHover: false,
            zoomToBoundsOnClick: true
        });

        // Custom unavailable driver icon
        const unavailableDriverIcon = L.divIcon({
            className: 'unavailable-driver-marker',
            html: '<div style="background-color: #f44336; width: 16px; height: 16px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3);"></div>',
            iconSize: [22, 22],
            iconAnchor: [11, 11]
        });

        // Store markers by driver ID
        const unavailableDriverMarkers = {};

        // Function to add or update an unavailable driver marker
        function updateUnavailableDriverMarker(driverId, driverData) {
            const lat = parseFloat(driverData.latitude);
            const lng = parseFloat(driverData.longitude);
            
            if (isNaN(lat) || isNaN(lng)) return;

            // If marker exists, update its position
            if (unavailableDriverMarkers[driverId]) {
                unavailableDriverMarkers[driverId].setLatLng([lat, lng]);
            } else {
                // Create new marker
                const marker = L.marker([lat, lng], {
                    icon: unavailableDriverIcon,
                    title: `Driver #${driverId}`
                });
                
                marker.bindPopup(`
                    <div style="text-align: center; min-width: 120px;">
                        <strong>{{ __('Driver') }} #${driverId}</strong><br>
                        <span class="badge bg-danger mt-1">{{ __('Offline') }} 🔴</span><br>
                        <small class="text-muted">Lat: ${lat.toFixed(6)}, Lng: ${lng.toFixed(6)}</small>
                    </div>
                `);
                
                markers.addLayer(marker);
                unavailableDriverMarkers[driverId] = marker;
            }
        }

        // Function to remove an unavailable driver marker
        function removeUnavailableDriverMarker(driverId) {
            if (unavailableDriverMarkers[driverId]) {
                markers.removeLayer(unavailableDriverMarkers[driverId]);
                delete unavailableDriverMarkers[driverId];
            }
        }

        // Add initial markers
        Object.entries(initialUnavailableDrivers).forEach(([driverId, driverData]) => {
            updateUnavailableDriverMarker(driverId, driverData);
        });

        // Add marker cluster to map
        map.addLayer(markers);

        // Set up Firebase real-time listeners
        if (firebaseApp && firebase.database) {
            const unavailableDriversRef = firebase.database().ref('unavailable_drivers');
            
            // Listen for new unavailable drivers
            unavailableDriversRef.on('child_added', (snapshot) => {
                const driverId = snapshot.key;
                const driverData = snapshot.val();
                
                if (driverData && driverData.latitude && driverData.longitude) {
                    // Normalize driver ID
                    let normalizedId = driverId;
                    if (isNaN(driverId)) {
                        const match = driverId.match(/(\d+)/);
                        if (match) normalizedId = match[1];
                    }
                    
                    updateUnavailableDriverMarker(normalizedId, driverData);
                    
                    // Update badge count
                    updateUnavailableDriverCount();
                }
            });

            // Listen for unavailable driver updates
            unavailableDriversRef.on('child_changed', (snapshot) => {
                const driverId = snapshot.key;
                const driverData = snapshot.val();
                
                if (driverData && driverData.latitude && driverData.longitude) {
                    let normalizedId = driverId;
                    if (isNaN(driverId)) {
                        const match = driverId.match(/(\d+)/);
                        if (match) normalizedId = match[1];
                    }
                    
                    updateUnavailableDriverMarker(normalizedId, driverData);
                }
            });

            // Listen for unavailable driver removal (went online)
            unavailableDriversRef.on('child_removed', (snapshot) => {
                const driverId = snapshot.key;
                
                let normalizedId = driverId;
                if (isNaN(driverId)) {
                    const match = driverId.match(/(\d+)/);
                    if (match) normalizedId = match[1];
                }
                
                removeUnavailableDriverMarker(normalizedId);
                
                // Update badge count
                updateUnavailableDriverCount();
            });
        }

        // Function to update unavailable driver count badge
        function updateUnavailableDriverCount() {
            const count = Object.keys(unavailableDriverMarkers).length;
            const badge = document.getElementById('unavailable-drivers-badge');
            if (badge) {
                badge.innerHTML = `<i class="fa fa-circle text-danger me-1" style="font-size: 0.5rem;"></i>${count} / {{ $driverCount }}`;
            }
        }

        // Cleanup on page unload
        window.addEventListener('beforeunload', () => {
            if (firebase.database) {
                firebase.database().ref('unavailable_drivers').off();
            }
        });
    })();
    </script>
    @endif
@endpush
