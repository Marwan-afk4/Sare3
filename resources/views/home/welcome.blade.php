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

    <!-- Drivers by City Section -->
    <div class="main-card mb-3 card">
        <div class="card-header border-bottom py-3">
            <h4 class="mb-0">{{ __('Drivers by City') }}</h4>
            <p class="text-body-tertiary mb-0 mt-1">{{ __('Distribution of drivers across Jordan cities') }}</p>
        </div>
        <div class="card-body">
            <div class="row g-3">
                @php $hasDrivers = false; @endphp
                @foreach ($citiesWithDriverCount as $city)
                    @if ($city->driver_count > 0)
                        @php $hasDrivers = true; @endphp
                        <div class="col-xl-3 col-md-4 col-sm-6 col-12">
                            <a href="{{ route('drivers.index', ['city' => $city->id]) }}" class="text-decoration-none card h-100 city-card border rounded-3 p-3 bg-body-highlight">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="avatar avatar-l bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center text-primary" style="width: 42px; height: 42px;">
                                            <i class="fa fa-map-marker-alt fs-7"></i>
                                        </div>
                                        <div>
                                            <h5 class="mb-0 text-body-emphasis fw-bold" style="font-size: 0.95rem;">{{ $city->name }}</h5>
                                            <span class="fs-10 text-body-tertiary">{{ __('Jordan') }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="badge bg-primary-subtle text-primary fs-8 px-3 py-2 rounded-pill fw-bold">{{ $city->driver_count }}</span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endif
                @endforeach
                
                @if ($driversWithNoCityCount > 0)
                    @php $hasDrivers = true; @endphp
                    <div class="col-xl-3 col-md-4 col-sm-6 col-12">
                        <a href="{{ route('drivers.index', ['city' => 'no_city']) }}" class="text-decoration-none card h-100 city-card city-card-no-city border border-warning-subtle rounded-3 p-3 bg-body-highlight">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar avatar-l bg-warning-subtle rounded-circle d-flex align-items-center justify-content-center text-warning" style="width: 42px; height: 42px;">
                                        <i class="fa fa-question-circle fs-7"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-0 text-warning fw-bold" style="font-size: 0.95rem;">{{ __('No City') }}</h5>
                                        <span class="fs-10 text-body-tertiary">{{ __('Unassigned') }}</span>
                                    </div>
                                </div>
                                <div>
                                    <span class="badge bg-warning-subtle text-warning fs-8 px-3 py-2 rounded-pill fw-bold">{{ $driversWithNoCityCount }}</span>
                                </div>
                            </div>
                        </a>
                    </div>
                @endif

                @if (!$hasDrivers)
                    <div class="col-12 text-center py-4">
                        <p class="text-muted mb-0">{{ __('No drivers registered in any city yet.') }}</p>
                    </div>
                @endif
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
                        <span id="ws-status" class="badge bg-secondary me-2">⏳ Connecting...</span>
                        <span class="badge bg-success" id="available-drivers-badge" style="font-size: 1.2rem; padding: 0.5rem 1rem;">
                            <i class="fa fa-circle text-success me-1" style="font-size: 0.5rem;"></i>
                            {{ $availableDriversCount }} / {{ $driverCount }}
                        </span>
                    </div>
                    <small class="text-muted">{{ __('Online Drivers') }}</small>
                    {{-- <div class="mt-1">
                        <button class="btn btn-sm btn-outline-primary" onclick="addTestMarker()">Test Map Marker</button>
                    </div> --}}
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div id="available-drivers-map" style="height: 500px; width: 100%; border-radius: 0 0 8px 8px; border: 1px solid #e0e0e0;"></div>
            <div class="m-2 text-center">
                <small class="text-muted">
                    <i class="fa fa-wifi"></i> {{ __('Map updates in real-time via WebSocket') }}
                </small>
            </div>
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
        <div class="card-body p-0">
            <div id="unavailable-drivers-map" style="height: 500px; width: 100%; border-radius: 0 0 8px 8px; border: 1px solid #e0e0e0;"></div>
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
    .city-card {
        cursor: pointer;
        transition: all 0.2s ease-in-out;
    }
    .city-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.06);
        border-color: var(--bs-primary, #4f46e5) !important;
    }
    .city-card-no-city:hover {
        border-color: var(--bs-warning, #f59e0b) !important;
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
    <!-- Laravel Echo + Reverb loaded below -->

    @php
        $safeUserCounts = $userMonthlyCounts ?? [12, 15, 20, 18, 22, 30, 25, 28, 24, 26, 30, 33];
        $safeDriverCounts = $driverMonthlyCounts ?? [5, 7, 9, 6, 10, 12, 8, 11, 9, 13, 14, 16];
    @endphp

    <script>
        function renderLineChart(selector, seriesName, seriesData, themeColor) {
            const months = [
                "January", "February", "March", "April", "May", "June",
                "July", "August", "September", "October", "November", "December"
            ];

            const el = document.querySelector(selector);
            if (!el) return;

            if (echarts.getInstanceByDom(el)) {
                echarts.getInstanceByDom(el).dispose();
            }

            // ✅ detect dark mode
            const isDark = localStorage.getItem('phoenixTheme') === 'dark';

            const chart = echarts.init(el, null, {
                backgroundColor: 'transparent'
            });

            // Premium Color Schemes
            let lineColor, areaColor, shadowColor;
            if (themeColor === 'blue') {
                lineColor = isDark ? '#6366f1' : '#4f46e5';
                areaColor = isDark ? 'rgba(99, 102, 241, 0.2)' : 'rgba(79, 70, 229, 0.15)';
                shadowColor = isDark ? 'rgba(99, 102, 241, 0.3)' : 'rgba(79, 70, 229, 0.2)';
            } else {
                lineColor = isDark ? '#34d399' : '#10b981';
                areaColor = isDark ? 'rgba(52, 211, 153, 0.2)' : 'rgba(16, 185, 129, 0.15)';
                shadowColor = isDark ? 'rgba(52, 211, 153, 0.3)' : 'rgba(16, 185, 129, 0.2)';
            }

            chart.setOption({
                grid: {
                    left: '2%',
                    right: '2%',
                    bottom: '3%',
                    top: '10%',
                    containLabel: true
                },
                tooltip: {
                    trigger: 'axis',
                    backgroundColor: isDark ? '#1e293b' : '#ffffff',
                    borderColor: isDark ? '#334155' : '#e2e8f0',
                    borderWidth: 1,
                    padding: [8, 12],
                    textStyle: {
                        color: isDark ? '#f8fafc' : '#0f172a',
                        fontFamily: 'Inter, system-ui, -apple-system, sans-serif',
                        fontSize: 12
                    },
                    extraCssText: 'box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1); border-radius: 8px;',
                    formatter: function(params) {
                        return `
                            <div style="font-weight: 600; margin-bottom: 4px; font-size: 11px; text-transform: uppercase; color: ${isDark ? '#94a3b8' : '#64748b'};">${params[0].name}</div>
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background-color: ${lineColor};"></span>
                                <span style="color: ${isDark ? '#e2e8f0' : '#334155'};">${params[0].seriesName}: <strong style="color: ${isDark ? '#ffffff' : '#0f172a'};">${params[0].value}</strong></span>
                            </div>
                        `;
                    }
                },
                xAxis: {
                    type: 'category',
                    data: months,
                    boundaryGap: false,
                    axisLine: { show: false },
                    axisTick: { show: false },
                    axisLabel: {
                        color: isDark ? '#94a3b8' : '#64748b',
                        formatter: value => value.substring(0, 3),
                        fontFamily: 'inherit',
                        fontSize: 11,
                        margin: 10
                    }
                },
                yAxis: {
                    type: 'value',
                    min: 0,
                    axisLine: { show: false },
                    axisTick: { show: false },
                    splitLine: {
                        lineStyle: {
                            color: isDark ? '#334155' : '#f1f5f9',
                            type: 'dashed'
                        }
                    },
                    axisLabel: {
                        color: isDark ? '#94a3b8' : '#64748b',
                        fontFamily: 'inherit',
                        fontSize: 11,
                        margin: 10
                    }
                },
                series: [{
                    name: seriesName,
                    type: 'line',
                    data: seriesData,
                    smooth: true,
                    showSymbol: false,
                    symbol: 'circle',
                    symbolSize: 8,
                    lineStyle: {
                        width: 3.5,
                        color: lineColor,
                        shadowColor: shadowColor,
                        shadowBlur: 10,
                        shadowOffsetY: 5
                    },
                    itemStyle: {
                        color: lineColor,
                        borderWidth: 2,
                        borderColor: isDark ? '#1e293b' : '#ffffff'
                    },
                    areaStyle: {
                        color: {
                            type: 'linear',
                            x: 0,
                            y: 0,
                            x2: 0,
                            y2: 1,
                            colorStops: [
                                { offset: 0, color: areaColor },
                                { offset: 1, color: 'rgba(255, 255, 255, 0)' }
                            ]
                        }
                    }
                }]
            });
        }

        function initHomePageScripts() {
            feather.replace();

            const userMonthlyCounts = @json($safeUserCounts);
            const driverMonthlyCounts = @json($safeDriverCounts);

            renderLineChart('.echarts-new-users', 'Users', userMonthlyCounts, 'blue');
            renderLineChart('.echarts-new-drivers', 'Drivers', driverMonthlyCounts, 'emerald');
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

    <!-- Real-time driver map via Reverb WebSocket -->
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/pusher-js@8.4.0/dist/web/pusher.min.js"></script>
    <script>
    (function () {
        // ─── Driver name lookup (server-rendered) ────────────────────────────────
        const driverNames = @json($driverNames ?? []);

        // ─── Available Drivers Map ────────────────────────────────────────────────
        let availableMap, availableMarkers;
        let availableDriverMarkers = {};
        const availableMapEl = document.getElementById('available-drivers-map');

        if (availableMapEl) {
            try {
                const initialAvailable = @json($availableDrivers);

                let cLat = 30.0444, cLng = 31.2357; // Default to Cairo
                const avVals = Object.values(initialAvailable);
                if (avVals.length) {
                    cLat = avVals.reduce((s, d) => s + (d.latitude || d.lat), 0) / avVals.length;
                    cLng = avVals.reduce((s, d) => s + (d.longitude || d.lng), 0) / avVals.length;
                }

                console.log("🗺️ Initializing Available Map...");
                availableMap = L.map('available-drivers-map').setView([cLat, cLng], 12);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors', maxZoom: 19
                }).addTo(availableMap);

                availableMarkers = L.layerGroup().addTo(availableMap);

                setTimeout(() => { availableMap.invalidateSize(); }, 500);
            } catch (e) {
                console.error("Leaflet Available Map Error:", e);
            }
        }

        // ─── Unavailable Drivers Map ──────────────────────────────────────────────
        let unavailableMap, unavailableMarkers;
        let unavailableDriverMarkers = {};
        const unavailableMapEl = document.getElementById('unavailable-drivers-map');

        if (unavailableMapEl) {
            try {
                const initialUnavailable = @json($unavailableDrivers);

                let uLat = 30.0444, uLng = 31.2357; // Default to Cairo
                const unVals = Object.values(initialUnavailable);
                if (unVals.length) {
                    uLat = unVals.reduce((s, d) => s + (d.latitude || d.lat), 0) / unVals.length;
                    uLng = unVals.reduce((s, d) => s + (d.longitude || d.lng), 0) / unVals.length;
                }

                console.log("🗺️ Initializing Unavailable Map...");
                unavailableMap = L.map('unavailable-drivers-map').setView([uLat, uLng], 12);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors', maxZoom: 19
                }).addTo(unavailableMap);

                unavailableMarkers = L.layerGroup().addTo(unavailableMap);

                setTimeout(() => { unavailableMap.invalidateSize(); }, 500);
            } catch (e) {
                console.error("Leaflet Unavailable Map Error:", e);
            }
        }

        // ─── Icon factories ───────────────────────────────────────────────────────
        // ─── Icon factories ───────────────────────────────────────────────────────
        function makeIcon(color) {
            // A professional top-down car SVG
            const carSvg = `
                <svg width="36" height="36" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                    <rect x="25" y="10" width="50" height="80" rx="15" fill="${color}" stroke="#fff" stroke-width="4"/>
                    <rect x="30" y="25" width="40" height="25" rx="5" fill="#333" opacity="0.8"/>
                    <rect x="30" y="60" width="40" height="15" rx="3" fill="#333" opacity="0.8"/>
                    <rect x="20" y="20" width="5" height="15" rx="2" fill="#fff" opacity="0.5"/>
                    <rect x="75" y="20" width="5" height="15" rx="2" fill="#fff" opacity="0.5"/>
                    <rect x="20" y="70" width="5" height="10" rx="2" fill="red" opacity="0.8"/>
                    <rect x="75" y="70" width="5" height="10" rx="2" fill="red" opacity="0.8"/>
                </svg>
            `;
            
            return L.divIcon({
                html: `<div class="car-icon-wrapper" style="width:36px;height:36px;">${carSvg}</div>`,
                iconSize: [36, 36], 
                iconAnchor: [18, 18],
                className: ''
            });
        }
        const greenIcon = makeIcon('#2ecc71'); // Modern Emerald Green
        const redIcon   = makeIcon('#e74c3c'); // Modern Alizarin Red

        // ─── Helper: upsert marker in "available" map ─────────────────────────────
        function upsertAvailableMarker(driverId, data) {
            if (!availableMap) return;
            const lat  = parseFloat(data.latitude ?? data.lat);
            const lng  = parseFloat(data.longitude ?? data.lng);
            const name = data.name || driverNames[driverId] || `{{ __('Driver') }} #${driverId}`;
            if (isNaN(lat) || isNaN(lng)) return;

            const bearing = data.bearing || 0;
            const rotate = `transform: rotate(${bearing}deg); transition: transform 0.3s ease;`;

            if (availableDriverMarkers[driverId]) {
                availableDriverMarkers[driverId].setLatLng([lat, lng]);
                const iconElement = availableDriverMarkers[driverId].getElement();
                if (iconElement) {
                    const wrapper = iconElement.querySelector('.car-icon-wrapper');
                    if (wrapper) wrapper.style.transform = `rotate(${bearing}deg)`;
                }
                availableDriverMarkers[driverId].getPopup().setContent(popupHtml(name, lat, lng, true));
            } else {
                const bearing = data.bearing || 0;
                const m = L.marker([lat, lng], { icon: greenIcon, title: name });
                m.on('add', function() {
                    const el = m.getElement().querySelector('.car-icon-wrapper');
                    if (el) el.style.transform = `rotate(${bearing}deg)`;
                });
                m.bindPopup(popupHtml(name, lat, lng, true));
                availableMarkers.addLayer(m);
                availableDriverMarkers[driverId] = m;
                updateAvailableBadge();
            }
        }

        // ─── Helper: upsert marker in "unavailable" map ───────────────────────────
        function upsertUnavailableMarker(driverId, data) {
            if (!unavailableMap) return;
            const lat  = parseFloat(data.latitude ?? data.lat);
            const lng  = parseFloat(data.longitude ?? data.lng);
            const name = data.name || driverNames[driverId] || `{{ __('Driver') }} #${driverId}`;
            if (isNaN(lat) || isNaN(lng)) return;

            if (unavailableDriverMarkers[driverId]) {
                unavailableDriverMarkers[driverId].setLatLng([lat, lng]);
                unavailableDriverMarkers[driverId].getPopup().setContent(popupHtml(name, lat, lng, false));
            } else {
                const m = L.marker([lat, lng], { icon: redIcon, title: name });
                m.bindPopup(popupHtml(name, lat, lng, false));
                unavailableMarkers.addLayer(m);
                unavailableDriverMarkers[driverId] = m;
                updateUnavailableBadge();
            }
        }

        function removeAvailableMarker(driverId) {
            if (availableDriverMarkers[driverId]) {
                availableMarkers.removeLayer(availableDriverMarkers[driverId]);
                delete availableDriverMarkers[driverId];
                updateAvailableBadge();
            }
        }

        function removeUnavailableMarker(driverId) {
            if (unavailableDriverMarkers[driverId]) {
                unavailableMarkers.removeLayer(unavailableDriverMarkers[driverId]);
                delete unavailableDriverMarkers[driverId];
                updateUnavailableBadge();
            }
        }

        window.addTestMarker = function() {
            if (!availableMap) return alert('Map not ready');
            const center = availableMap.getCenter();
            L.marker(center, {
                icon: L.divIcon({
                    html: '<div style="background:#007bff;width:40px;height:40px;border-radius:50%;border:4px solid #fff;box-shadow:0 0 20px rgba(0,123,255,0.8);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:bold">TEST</div>',
                    iconSize: [48, 48], iconAnchor: [24, 24]
                })
            }).addTo(availableMap).bindPopup("<b>Map Rendering is Working!</b>").openPopup();
            alert('Test marker added to map center!');
        };

        function popupHtml(name, lat, lng, online) {
            const badge = online
                ? '<span class="badge bg-success mt-1">{{ __("Online") }} 🟢</span>'
                : '<span class="badge bg-danger mt-1">{{ __("Offline") }} 🔴</span>';
            return `<div style="text-align:center;min-width:120px"><strong>${name}</strong><br>${badge}<br><small class="text-muted">Lat: ${lat.toFixed(6)}, Lng: ${lng.toFixed(6)}</small></div>`;
        }

        function updateAvailableBadge() {
            const el = document.getElementById('available-drivers-badge');
            if (el) el.innerHTML = `<i class="fa fa-circle text-success me-1" style="font-size:.5rem"></i>${Object.keys(availableDriverMarkers).length} / {{ $driverCount }}`;
        }

        function updateUnavailableBadge() {
            const el = document.getElementById('unavailable-drivers-badge');
            if (el) el.innerHTML = `<i class="fa fa-circle text-danger me-1" style="font-size:.5rem"></i>${Object.keys(unavailableDriverMarkers).length} / {{ $driverCount }}`;
        }

        // ─── Initial Seeding (Must be at the end) ─────────────────────────────────
        console.log("🚀 Seeding initial drivers...");
        if (availableMapEl) {
            const availableData = @json($availableDrivers);
            Object.entries(availableData).forEach(([id, d]) => upsertAvailableMarker(id, d));
            if (Object.keys(availableDriverMarkers).length > 0) {
                const group = new L.featureGroup(Object.values(availableDriverMarkers));
                availableMap.fitBounds(group.getBounds().pad(0.2));
            }
        }
        if (unavailableMapEl) {
            const unavailableData = @json($unavailableDrivers);
            Object.entries(unavailableData).forEach(([id, d]) => upsertUnavailableMarker(id, d));
            if (Object.keys(unavailableDriverMarkers).length > 0) {
                const group = new L.featureGroup(Object.values(unavailableDriverMarkers));
                unavailableMap.fitBounds(group.getBounds().pad(0.2));
            }
        }

        // ─── Real-time: Laravel Echo → Reverb WebSocket ───────────────────────────
        const _wsPort = window.location.port ? parseInt(window.location.port) : (window.location.protocol === 'https:' ? 443 : 80);
        const _useTLS = window.location.protocol === 'https:';
        const echoConfig = {
            broadcaster:       'reverb',
            key:               '{{ config("broadcasting.connections.reverb.key") }}',
            wsHost:            window.location.hostname,
            wsPort:            _wsPort,
            wssPort:           _wsPort,
            forceTLS:          _useTLS,
            enabledTransports: ['ws', 'wss'],
        };
        console.log('📡 Initializing Echo with config:', echoConfig);
        
        const echo = new Echo(echoConfig);

        // "driver-location" is a PUBLIC channel — no auth needed for admin dashboard
        echo.channel('driver-location')
            .listen('.driver.location.updated', (e) => {
                const id       = String(e.driver_id);
                const name     = driverNames[id] || `{{ __('Driver') }} #${id}`;
                const isOnMap  = availableDriverMarkers[id] !== undefined;
                const isUnavMap = unavailableDriverMarkers[id] !== undefined;

                // Move marker on the correct map.
                // (We don't know is_available here — just update whatever map already shows this driver.
                //  If the driver is on neither map yet, add to available map as a live signal.)
                if (isOnMap) {
                    upsertAvailableMarker(id, { ...e, name });
                } else if (isUnavMap) {
                    upsertUnavailableMarker(id, { ...e, name });
                } else {
                    // New driver — add to available map (driver is sending location = online)
                    upsertAvailableMarker(id, { ...e, name });
                }
            });

        // ─── Connection status indicator ──────────────────────────────────────────
        echo.connector.pusher.connection.bind('connected', () => {
            console.log('✅ WebSocket Connected');
            const badge = document.getElementById('ws-status');
            if (badge) { badge.className = 'badge bg-success'; badge.textContent = '🟢 Live'; }
        });
        echo.connector.pusher.connection.bind('disconnected', () => {
            console.log('🔴 WebSocket Disconnected');
            const badge = document.getElementById('ws-status');
            if (badge) { badge.className = 'badge bg-danger'; badge.textContent = '🔴 Disconnected'; }
        });
        echo.connector.pusher.connection.bind('error', (err) => {
            console.error('❌ WebSocket Error:', err);
            const badge = document.getElementById('ws-status');
            if (badge) { badge.className = 'badge bg-warning'; badge.textContent = '⚠️ Error'; }
        });
        echo.connector.pusher.connection.bind('connecting', () => {
            console.log('⏳ WebSocket Connecting...');
        });
    })();
    </script>
@endpush
