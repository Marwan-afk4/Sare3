@extends('layouts.app')
@php
    $currentPage = 'rides';
@endphp
@section('title', __('Rides'))

@push('styles')
<style>
    .status-dot {
        vertical-align: middle;
        margin-top: -2px;
    }
    .list-group-item-action {
        transition: all 0.2s ease-in-out;
    }
    .list-group-item-action:hover {
        padding-left: 1.25rem;
    }
    /* Custom Scrollbar for Modal details panel */
    .modal-details-panel::-webkit-scrollbar {
        width: 6px;
    }
    .modal-details-panel::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    .modal-details-panel::-webkit-scrollbar-thumb {
        background: #ccc;
        border-radius: 4px;
    }
    .modal-details-panel::-webkit-scrollbar-thumb:hover {
        background: #aaa;
    }
</style>
@endpush

@section('content')
    <div class="container-fluid">
        <h1 class="mb-4">{{ __('Rides') }}</h1>

        <div class="row">
            {{-- Left Column: Sidebar Filters for Statuses --}}
            <div class="col-xl-2 col-lg-3 col-md-12 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 fw-bold"><i class="fa fa-filter text-primary"></i> {{ __('Status Filters') }}</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <a href="{{ route('rides.index', request()->except('status')) }}" 
                               class="list-group-item list-group-item-action d-flex justify-content-between align-items-center border-0 border-bottom {{ !request('status') ? 'active bg-primary text-white' : '' }}">
                                <span><i class="fa fa-list me-2"></i> {{ __('All') }}</span>
                                <span class="badge rounded-pill {{ !request('status') ? 'bg-light text-dark' : 'bg-secondary' }}">
                                    {{ array_sum($ridesStatusCounts) }}
                                </span>
                            </a>
                            @foreach ($rideStatuses as $status)
                                <a href="{{ route('rides.index', array_merge(request()->except('status'), ['status' => $status->value])) }}" 
                                   class="list-group-item list-group-item-action d-flex justify-content-between align-items-center border-0 border-bottom {{ request('status') === $status->value ? 'active' : '' }}"
                                   @if(request('status') === $status->value)
                                   style="background-color: #{{ $status->color() }}; color: #{{ $status->textColor() }}; font-weight: bold;"
                                   @endif>
                                    <span>
                                        <span class="status-dot d-inline-block rounded-circle me-2" style="width: 10px; height: 10px; background-color: #{{ $status->color() }}; border: 1px solid #{{ request('status') === $status->value ? $status->textColor() : 'ced4da' }}"></span>
                                        {{ $status->label() }}
                                    </span>
                                    <span class="badge rounded-pill {{ request('status') === $status->value ? 'bg-light text-dark' : 'bg-secondary' }}"
                                          @if(request('status') !== $status->value)
                                          style="background-color: #f1f3f5; color: #495057;"
                                          @endif>
                                        {{ $ridesStatusCounts[$status->value] ?? '0' }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Main Content Area (Original full-width tables & forms layout) --}}
            <div class="col-xl-10 col-lg-9 col-md-12 mb-4">
                {{-- Original Filters Card --}}
                <div class="card mb-3 shadow-sm border-0">
                    <div class="card-body">
                        <form action="{{ route('rides.index') }}" method="GET" id="filterForm">
                            <div class="row g-3 align-items-end">
                                {{-- Keyword Search --}}
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">{{ __('Search') }}</label>
                                    <div class="input-group">
                                        <input type="text" name="keyword" class="form-control" autocomplete="off"
                                            placeholder="{{ __('Keyword') }}..." value="{{ request('keyword') }}">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fa fa-search"></i>
                                        </button>
                                    </div>
                                </div>

                                {{-- Min KM Filter --}}
                                <div class="col-md-2">
                                    <label class="form-label fw-bold">{{ __('Min KM') }}</label>
                                    <input type="number" name="min_km" class="form-control" step="0.1" min="0"
                                        placeholder="{{ __('Min') }}" value="{{ request('min_km') }}">
                                </div>

                                {{-- Max KM Filter --}}
                                <div class="col-md-2">
                                    <label class="form-label fw-bold">{{ __('Max KM') }}</label>
                                    <input type="number" name="max_km" class="form-control" step="0.1" min="0"
                                        placeholder="{{ __('Max') }}" value="{{ request('max_km') }}">
                                </div>

                                {{-- Apply Filter Button --}}
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="fa fa-filter"></i> {{ __('Apply Filter') }}
                                    </button>
                                </div>

                                {{-- Clear Filter Button --}}
                                <div class="col-md-2">
                                    @if (request('keyword') || request('min_km') || request('max_km') || request('status') || request('accepted_after_seconds') || request('cancelled_before_accept') || request('min_offers'))
                                        <a href="{{ route('rides.index') }}" class="btn btn-secondary w-100">
                                            <i class="fa fa-times"></i> {{ __('Clear All') }}
                                        </a>
                                    @endif
                                </div>
                            </div>

                            {{-- Offer-history filters --}}
                            <div class="row g-3 align-items-end mt-1">
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">{{ __('Accepted after (seconds)') }}</label>
                                    <input type="number" name="accepted_after_seconds" min="0" step="1"
                                        class="form-control" placeholder="e.g. 60"
                                        value="{{ request('accepted_after_seconds') }}">
                                    <small class="text-muted">{{ __('Rides whose accepting captain took at least N seconds.') }}</small>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label fw-bold">{{ __('Min captains offered') }}</label>
                                    <input type="number" name="min_offers" min="1" step="1"
                                        class="form-control" placeholder="e.g. 3"
                                        value="{{ request('min_offers') }}">
                                    <small class="text-muted">{{ __('Rides that cycled through at least N captains.') }}</small>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-bold">{{ __('Cancelled before any accept') }}</label>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox"
                                            id="cancelled_before_accept"
                                            name="cancelled_before_accept" value="1"
                                            {{ request('cancelled_before_accept') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="cancelled_before_accept">
                                            {{ __('Show only rides user cancelled before any captain accepted') }}
                                            @if(isset($cancelledBeforeAcceptCount))
                                                <span class="badge bg-dark">{{ $cancelledBeforeAcceptCount }}</span>
                                            @endif
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-outline-primary w-100">
                                        <i class="fa fa-sliders"></i> {{ __('Apply') }}
                                    </button>
                                </div>
                            </div>

                            {{-- Preserve Status Filter --}}
                            @if(request('status'))
                                <input type="hidden" name="status" value="{{ request('status') }}">
                            @endif
                        </form>
                    </div>
                </div>

                {{-- Rides Table Card --}}
                <div class="main-card mb-3 card shadow-sm border-0">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="mb-0 table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>
                                            <a href="{{ route('rides.index', ['sort' => 'id', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
                                                {{ __('Id') }}
                                                @if ($sortField === 'id')
                                                    <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                                                @endif
                                            </a>
                                        </th>
                                        <th>
                                            <a href="{{ route('rides.index', ['sort' => 'user_id', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
                                                {{ __('User') }}
                                                @if ($sortField === 'user_id')
                                                    <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                                                @endif
                                            </a>
                                        </th>
                                        <th>
                                            <a href="{{ route('rides.index', ['sort' => 'driver_id', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
                                                {{ __('Driver') }}
                                                @if ($sortField === 'driver_id')
                                                    <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                                                @endif
                                            </a>
                                        </th>
                                        <th>
                                            <a href="{{ route('rides.index', ['sort' => 'car_category_id', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
                                                {{ __('Car Category') }}
                                                @if ($sortField === 'car_category_id')
                                                    <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                                                @endif
                                            </a>
                                        </th>
                                        <th>
                                            <a href="{{ route('rides.index', ['sort' => 'pickup_address', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
                                                {{ __('Pickup Address') }}
                                                @if ($sortField === 'pickup_address')
                                                    <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                                                @endif
                                            </a>
                                        </th>
                                        <th>
                                            <a href="{{ route('rides.index', ['sort' => 'dropoff_address', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
                                                {{ __('Dropoff Address') }}
                                                @if ($sortField === 'dropoff_address')
                                                    <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                                                @endif
                                            </a>
                                        </th>
                                        <th>
                                            <a href="{{ route('rides.index', ['sort' => 'total_distance_in_km', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
                                                {{ __('Distance') }}
                                                @if ($sortField === 'estimated_km')
                                                    <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                                                @endif
                                            </a>
                                        </th>
                                        <th>
                                            <a href="{{ route('rides.index', ['sort' => 'time_taken', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
                                                {{ __('Time Taken') }}
                                                @if ($sortField === 'time_taken')
                                                    <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                                                @endif
                                            </a>
                                        </th>
                                        <th>
                                            <a href="{{ route('rides.index', ['sort' => 'calculated_final_price', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
                                                {{ __('Fare') }}
                                                @if ($sortField === 'calculated_final_price')
                                                    <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                                                @endif
                                            </a>
                                        </th>
                                        <th>
                                            <a href="{{ route('rides.index', ['sort' => 'status', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
                                                {{ __('Status') }}
                                                @if ($sortField === 'status')
                                                    <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                                                @endif
                                            </a>
                                        </th>
                                        <th>
                                            {{ __('Captain Status') }}
                                        </th>
                                        <th>
                                            <a href="{{ route('rides.index', ['sort' => 'created_at', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
                                                {{ __('Created At') }}
                                                @if ($sortField === 'created_at')
                                                    <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                                                @endif
                                            </a>
                                        </th>
                                        <th class="text-center">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($rides as $ride)
                                        <tr>
                                            <td>{{ $ride->id }}</td>
                                            <td>
                                                @if ($ride->user?->name)
                                                    <a href="{{ route('users.show', $ride->user) }}">{{ $ride->user->name }}</a>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($ride->driver?->name)
                                                    <a href="{{ route('drivers.show', $ride->driver) }}">{{ $ride->driver->name }}</a>
                                                @endif
                                                @if (($ride->offers_count ?? 0) > 0)
                                                    <div>
                                                        <small class="text-muted" title="{{ __('Captains this ride was offered to') }}">
                                                            <i class="fa fa-users"></i>
                                                            {{ trans_choice('{1} :count captain saw it|[2,*] :count captains saw it', $ride->offers_count, ['count' => $ride->offers_count]) }}
                                                        </small>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($ride->carCategory?->name)
                                                    <a href="{{ route('car-categories.show', $ride->carCategory) }}">
                                                        {{ $ride->carCategory->name }}
                                                    </a>
                                                @endif
                                            </td>
                                            <td>{{ $ride->pickup_address }}</td>
                                            <td>{{ $ride->dropoff_address }}</td>
                                            <td>{{ $ride->total_distance_in_km }} {{ __('km') }}</td>
                                            <td>{{ $ride->time_taken }} {{ __('min') }}</td>
                                            <td>
                                                @if ($ride->coupon_id && $ride->calculated_initial_price && $ride->calculated_final_price)
                                                    <div>
                                                        <span class="text-muted">{{ __('Before') }}:</span>
                                                        <strong>${{ number_format($ride->calculated_initial_price, 2) }}</strong>
                                                        <br>
                                                        <span class="text-muted">{{ __('After') }}:</span>
                                                        <strong class="text-success">${{ number_format($ride->calculated_final_price, 2) }}</strong>
                                                    </div>
                                                @elseif ($ride->calculated_final_price)
                                                    <strong>${{ number_format($ride->calculated_final_price, 2) }}</strong>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>{!! $ride->status->badge() !!}</td>
                                            <td>
                                                @if ($ride->driver)
                                                    <span class="badge bg-success mb-1" title="{{ __('Accepted by :driver', ['driver' => $ride->driver->name]) }}">
                                                        <i class="fa fa-check-circle"></i> {{ __('Accepted') }}
                                                    </span>
                                                @endif
                                                @if (($ride->rejected_count ?? 0) > 0)
                                                    <span class="badge bg-danger mb-1" title="{{ __('Rejected by :count captains', ['count' => $ride->rejected_count]) }}">
                                                        <i class="fa fa-times-circle"></i> {{ __('Rejected: :count', ['count' => $ride->rejected_count]) }}
                                                    </span>
                                                @endif
                                                @if (($ride->timeout_count ?? 0) > 0)
                                                    <span class="badge bg-warning text-dark mb-1" title="{{ __('Timed out for :count captains', ['count' => $ride->timeout_count]) }}">
                                                        <i class="fa fa-clock"></i> {{ __('Timeout: :count', ['count' => $ride->timeout_count]) }}
                                                    </span>
                                                @endif
                                                @if (!$ride->driver && ($ride->rejected_count ?? 0) === 0 && ($ride->timeout_count ?? 0) === 0)
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td><small>{{ $ride->created_at?->translatedFormat('d M Y - h:i A') ?? '' }}</small></td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <a href='{{ route('rides.show', $ride) }}' class="btn btn-subtle-primary btn-sm"
                                                        title="{{ __('View Details') }}">
                                                        <i class="fa fa-eye"></i>
                                                    </a>
                                                    @if ($ride->pickup_lat && $ride->pickup_lng)
                                                        <button type="button" class="btn btn-subtle-success btn-sm preview-route-btn" 
                                                                data-bs-toggle="modal" data-bs-target="#routeMapModal"
                                                                data-ride-id="{{ $ride->id }}"
                                                                data-pickup-lat="{{ $ride->pickup_lat }}"
                                                                data-pickup-lng="{{ $ride->pickup_lng }}"
                                                                data-pickup-address="{{ addslashes($ride->pickup_address ?? '') }}"
                                                                data-dropoff-lat="{{ $ride->dropoff_lat }}"
                                                                data-dropoff-lng="{{ $ride->dropoff_lng }}"
                                                                data-dropoff-address="{{ addslashes($ride->dropoff_address ?? '') }}"
                                                                data-status="{{ $ride->status->value }}"
                                                                data-status-label="{{ $ride->status->label() }}"
                                                                data-status-color="{{ $ride->status->color() }}"
                                                                data-status-text-color="{{ $ride->status->textColor() }}"
                                                                data-user="{{ $ride->user?->name ?? '' }}"
                                                                data-driver="{{ $ride->driver?->name ?? '' }}"
                                                                data-fare="{{ $ride->calculated_final_price ? '$'.number_format($ride->calculated_final_price, 2) : '-' }}"
                                                                data-distance="{{ $ride->total_distance_in_km ?? '' }}"
                                                                title="{{ __('Preview Route') }}">
                                                            @if (in_array($ride->status->value, ['in_progress', 'accepted', 'waiting_user']))
                                                                <i class="fa fa-location-arrow"></i>
                                                            @else
                                                                <i class="fa fa-route"></i>
                                                            @endif
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-3">
                            {{ $rides->appends(request()->query())->links('pagination::custom') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Route Preview Modal --}}
    <div class="modal fade" id="routeMapModal" tabindex="-1" aria-labelledby="routeMapModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
                <div class="modal-header bg-white border-bottom py-3 px-4">
                    <h5 class="modal-title fw-bold text-primary" id="routeMapModalLabel">
                        <i class="fa fa-map-marked-alt text-success me-2"></i>{{ __('Ride Route Preview') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="row g-0">
                        {{-- Left Column: Interactive Map --}}
                        <div class="col-md-8 position-relative" style="height: 550px;">
                            <div id="indexRideMap" style="height: 550px; width: 100%;"></div>
                            <div id="mapLoader" class="position-absolute top-50 start-50 translate-middle text-center bg-white p-3 rounded shadow" style="z-index: 10; border-radius: 8px;">
                                <div class="spinner-border text-primary" role="status"></div>
                                <p class="mt-2 mb-0 small text-muted">{{ __('Loading map & route data...') }}</p>
                            </div>
                        </div>
                        {{-- Right Column: Detailed Ride Sidebar --}}
                        <div class="col-md-4 bg-light border-start d-flex flex-column modal-details-panel" style="height: 550px; overflow-y: auto;">
                            <div class="p-3 border-bottom bg-white">
                                <h5 class="fw-bold mb-1 text-dark" id="modalRideId">Ride #</h5>
                                <div id="modalStatusBadge"></div>
                            </div>
                            <div class="p-3 flex-grow-1">
                                <div class="mb-3">
                                    <small class="text-muted text-uppercase fw-bold d-block mb-2" style="font-size: 0.75rem; letter-spacing: 0.5px;">{{ __('Route Locations') }}</small>
                                    <div class="d-flex align-items-start mb-2">
                                        <span class="badge bg-success me-2 mt-1" style="width: 20px; height: 20px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.75rem;">P</span>
                                        <div>
                                            <strong class="d-block small text-dark">{{ __('Pickup Address') }}</strong>
                                            <span class="small text-muted" id="modalPickupAddress"></span>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-start">
                                        <span class="badge bg-danger me-2 mt-1" style="width: 20px; height: 20px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.75rem;">D</span>
                                        <div>
                                            <strong class="d-block small text-dark">{{ __('Dropoff Address') }}</strong>
                                            <span class="small text-muted" id="modalDropoffAddress"></span>
                                        </div>
                                    </div>
                                </div>
                                
                                <hr class="my-3 text-muted" style="opacity: 0.15;">
                                
                                <div class="mb-3">
                                    <small class="text-muted text-uppercase fw-bold d-block mb-2" style="font-size: 0.75rem; letter-spacing: 0.5px;">{{ __('Details') }}</small>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <span class="text-muted d-block small" style="font-size: 0.75rem;">{{ __('Passenger') }}</span>
                                            <strong class="small text-dark" id="modalUserName">-</strong>
                                        </div>
                                        <div class="col-6">
                                            <span class="text-muted d-block small" style="font-size: 0.75rem;">{{ __('Driver') }}</span>
                                            <strong class="small text-dark" id="modalDriverName">-</strong>
                                        </div>
                                        <div class="col-6 mt-2">
                                            <span class="text-muted d-block small" style="font-size: 0.75rem;">{{ __('Distance') }}</span>
                                            <strong class="small text-dark" id="modalDistance">-</strong>
                                        </div>
                                        <div class="col-6 mt-2">
                                            <span class="text-muted d-block small" style="font-size: 0.75rem;">{{ __('Fare') }}</span>
                                            <strong class="small text-dark" id="modalFare">-</strong>
                                        </div>
                                    </div>
                                </div>

                                <hr class="my-3 text-muted" style="opacity: 0.15;">

                                <div>
                                    <small class="text-muted text-uppercase fw-bold d-block mb-2" style="font-size: 0.75rem; letter-spacing: 0.5px;">{{ __('Captain Offer History') }}</small>
                                    <div id="modalOfferLogs">
                                        <!-- Dynamically populated offer records -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
let previewMap = null;
let pickupMarker = null;
let dropoffMarker = null;
let routePolyline = null;
let toPickupPolyline = null;
let activeInterval = null;
let activeRideId = null;
let activeRideData = null;

// Listen to the bootstrap modal events
document.addEventListener('DOMContentLoaded', () => {
    const routeMapModalEl = document.getElementById('routeMapModal');
    if (routeMapModalEl) {
        routeMapModalEl.addEventListener('shown.bs.modal', async (event) => {
            // Button that triggered the modal
            const button = event.relatedTarget;
            if (!button) return;

            // Extract info from data-* attributes
            const rideId = button.dataset.rideId;
            const pickupLat = parseFloat(button.dataset.pickupLat);
            const pickupLng = parseFloat(button.dataset.pickupLng);
            const pickupAddr = button.dataset.pickupAddress;
            const dropoffLat = parseFloat(button.dataset.dropoffLat);
            const dropoffLng = parseFloat(button.dataset.dropoffLng);
            const dropoffAddr = button.dataset.dropoffAddress;
            const statusLabel = button.dataset.statusLabel;
            const statusColor = button.dataset.statusColor;
            const statusTextColor = button.dataset.statusTextColor;
            const statusVal = button.dataset.status;
            const userVal = button.dataset.user;
            const driverVal = button.dataset.driver;
            const fareVal = button.dataset.fare;
            const distanceVal = button.dataset.distance;

            activeRideId = rideId;
            activeRideData = {
                id: rideId,
                pickup: { lat: pickupLat, lng: pickupLng, address: pickupAddr },
                dropoff: { lat: dropoffLat, lng: dropoffLng, address: dropoffAddr },
                status: statusVal
            };

            // Populate static details in modal sidebar
            document.getElementById('modalRideId').textContent = '{{ __('Ride') }} #' + rideId;
            document.getElementById('modalPickupAddress').textContent = pickupAddr || '-';
            document.getElementById('modalDropoffAddress').textContent = dropoffAddr || '{{ __('No drop-off location selected') }}';
            document.getElementById('modalUserName').textContent = userVal || '-';
            document.getElementById('modalDriverName').textContent = driverVal || '{{ __('Not assigned') }}';
            document.getElementById('modalFare').textContent = fareVal || '-';
            document.getElementById('modalDistance').textContent = distanceVal ? (distanceVal + ' km') : '-';
            
            const badgeEl = document.getElementById('modalStatusBadge');
            badgeEl.className = 'badge rounded-pill px-3 py-2 mt-1';
            badgeEl.style.backgroundColor = '#' + statusColor;
            badgeEl.style.color = '#' + statusTextColor;
            badgeEl.textContent = statusLabel;

            // Clear previous offers list & show loader
            document.getElementById('modalOfferLogs').innerHTML = '<div class="text-center text-muted py-3"><i class="fa fa-spinner fa-spin"></i> {{ __('Loading offer history...') }}</div>';

            const loader = document.getElementById('mapLoader');
            if (loader) loader.classList.remove('d-none');

            // Initialize Google Map centered on pickup
            initOrCenterMap(pickupLat, pickupLng);

            // Render route segments and detailed stats
            await loadRouteDetails(rideId, pickupLat, pickupLng, dropoffLat, dropoffLng, statusVal);

            // Start live location polling if ride is active
            if (['in_progress', 'accepted', 'waiting_user'].includes(statusVal)) {
                activeInterval = setInterval(() => {
                    if (activeRideId === rideId) {
                        loadRouteDetails(rideId, pickupLat, pickupLng, dropoffLat, dropoffLng, statusVal);
                    }
                }, 15000);
            }
        });

        routeMapModalEl.addEventListener('hidden.bs.modal', () => {
            // Stop polling timer
            if (activeInterval) {
                clearInterval(activeInterval);
                activeInterval = null;
            }
            activeRideId = null;
            activeRideData = null;

            // Remove markers and polyline paths
            if (pickupMarker) pickupMarker.setMap(null);
            if (dropoffMarker) dropoffMarker.setMap(null);
            if (routePolyline) routePolyline.setMap(null);
            if (toPickupPolyline) toPickupPolyline.setMap(null);

            pickupMarker = null;
            dropoffMarker = null;
            routePolyline = null;
            toPickupPolyline = null;
        });
    }
});

function initOrCenterMap(lat, lng) {
    const mapElement = document.getElementById('indexRideMap');
    if (!mapElement) return;

    if (!previewMap) {
        previewMap = new google.maps.Map(mapElement, {
            zoom: 13,
            center: { lat: lat, lng: lng },
            mapTypeId: google.maps.MapTypeId.ROADMAP
        });
    } else {
        previewMap.setCenter({ lat: lat, lng: lng });
        previewMap.setZoom(13);
        google.maps.event.trigger(previewMap, 'resize');
    }
}

async function loadRouteDetails(rideId, pickupLat, pickupLng, dropoffLat, dropoffLng, status) {
    try {
        const response = await fetch(`/api/rides/${rideId}/tracking-data`);
        const loader = document.getElementById('mapLoader');
        
        if (loader) loader.classList.add('d-none');

        if (!response.ok) throw new Error('API request failed');
        const data = await response.json();

        if (activeRideId !== rideId) return;

        // Populate offer logs inside modal details panel
        const offersContainer = document.getElementById('modalOfferLogs');
        offersContainer.innerHTML = '';
        
        if (data.offers && data.offers.length > 0) {
            const listGroup = document.createElement('div');
            listGroup.className = 'list-group list-group-flush border rounded bg-white';
            
            data.offers.forEach((offer, index) => {
                const item = document.createElement('div');
                item.className = 'list-group-item p-2';
                
                // Color mapping for response states
                let badgeClass = 'bg-secondary';
                if (offer.response === 'accepted') badgeClass = 'bg-success';
                else if (offer.response === 'rejected') badgeClass = 'bg-danger';
                else if (offer.response === 'ignored') badgeClass = 'bg-warning text-dark'; // Timeout
                else if (offer.response === 'cancelled_by_user') badgeClass = 'bg-dark';
                
                const timeStr = offer.offered_at ? `<span class="text-muted float-end" style="font-size: 0.7rem;"><i class="fa fa-clock"></i> ${offer.offered_at}</span>` : '';
                const durationStr = offer.response_seconds !== null ? `<div class="text-muted" style="font-size: 0.7rem;">{{ __('Response time') }}: ${offer.response_seconds}s</div>` : '';
                
                item.innerHTML = `
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <strong class="text-dark small">${index + 1}. ${offer.driver_name}</strong>
                        ${timeStr}
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="badge ${badgeClass} p-1" style="font-size: 0.65rem;">${offer.response_label}</span>
                        ${durationStr}
                    </div>
                `;
                listGroup.appendChild(item);
            });
            offersContainer.appendChild(listGroup);
        } else {
            offersContainer.innerHTML = '<div class="text-muted py-2 text-center small"><i class="fa fa-info-circle"></i> {{ __('No offer records found for this ride.') }}</div>';
        }

        // Draw markers
        const bounds = new google.maps.LatLngBounds();
        bounds.extend({ lat: pickupLat, lng: pickupLng });

        if (pickupMarker) pickupMarker.setMap(null);
        pickupMarker = new google.maps.Marker({
            position: { lat: pickupLat, lng: pickupLng },
            map: previewMap,
            title: 'Pickup: ' + activeRideData.pickup.address,
            icon: {
                path: google.maps.SymbolPath.CIRCLE,
                scale: 9,
                fillColor: '#4CAF50',
                fillOpacity: 1,
                strokeColor: 'white',
                strokeWeight: 2
            },
            label: { text: 'P', color: 'white', fontWeight: 'bold' }
        });

        if (dropoffLat && dropoffLng) {
            bounds.extend({ lat: dropoffLat, lng: dropoffLng });
            if (dropoffMarker) dropoffMarker.setMap(null);
            dropoffMarker = new google.maps.Marker({
                position: { lat: dropoffLat, lng: dropoffLng },
                map: previewMap,
                title: 'Dropoff: ' + activeRideData.dropoff.address,
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: 9,
                    fillColor: '#F44336',
                    fillOpacity: 1,
                    strokeColor: 'white',
                    strokeWeight: 2
                },
                label: { text: 'D', color: 'white', fontWeight: 'bold' }
            });
        } else {
            if (dropoffMarker) {
                dropoffMarker.setMap(null);
                dropoffMarker = null;
            }
        }

        // Draw 'to pickup' dashed orange path
        const toPickupPoints = data.to_pickup_route_points || [];
        if (toPickupPoints.length >= 2) {
            const toPickupPath = toPickupPoints.map(p => ({ lat: parseFloat(p.lat), lng: parseFloat(p.lng) }));
            toPickupPath.forEach(p => bounds.extend(p));

            if (toPickupPolyline) toPickupPolyline.setMap(null);
            toPickupPolyline = new google.maps.Polyline({
                path: toPickupPath,
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
                }],
                map: previewMap
            });
        }

        // Draw main trip path
        const tripPoints = data.trip_route_points || data.route_points || [];
        if (tripPoints.length >= 2) {
            const tripPath = tripPoints.map(p => ({ lat: parseFloat(p.lat), lng: parseFloat(p.lng) }));
            tripPath.forEach(p => bounds.extend(p));

            if (routePolyline) routePolyline.setMap(null);
            routePolyline = new google.maps.Polyline({
                path: tripPath,
                geodesic: true,
                strokeColor: '#4CAF50',
                strokeOpacity: 1.0,
                strokeWeight: 4,
                map: previewMap
            });
        } else {
            // Fallback directions service
            drawDirectionsRoute(pickupLat, pickupLng, dropoffLat, dropoffLng);
        }

        // Fit map bounds
        previewMap.fitBounds(bounds);

    } catch (e) {
        console.warn('Error loading route points:', e);
        const loader = document.getElementById('mapLoader');
        if (loader) loader.classList.add('d-none');

        // Fallback Directions drawing
        drawDirectionsRoute(pickupLat, pickupLng, dropoffLat, dropoffLng);
    }
}

function drawDirectionsRoute(pickupLat, pickupLng, dropoffLat, dropoffLng) {
    if (!dropoffLat || !dropoffLng) return;

    const directionsService = new google.maps.DirectionsService();
    directionsService.route({
        origin: { lat: pickupLat, lng: pickupLng },
        destination: { lat: dropoffLat, lng: dropoffLng },
        travelMode: google.maps.TravelMode.DRIVING
    }, (result, status) => {
        if (status === 'OK' && activeRideId) {
            if (routePolyline) routePolyline.setMap(null);

            const routePath = result.routes[0].overview_path;
            routePolyline = new google.maps.Polyline({
                path: routePath,
                geodesic: true,
                strokeColor: '#2196F3',
                strokeOpacity: 0.8,
                strokeWeight: 4,
                map: previewMap
            });

            // Adjust bounds to fit route
            const bounds = new google.maps.LatLngBounds();
            routePath.forEach(p => bounds.extend(p));
            previewMap.fitBounds(bounds);
        }
    });
}
</script>

<!-- Google Maps API -->
@if(config('services.google_maps.api_key') && config('services.google_maps.api_key') !== 'your_actual_api_key_here')
<script async defer
    src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key') }}&callback=initOrCenterMap&libraries=marker">
</script>
@else
<script>
    function initOrCenterMap(lat, lng) {
        document.getElementById('indexRideMap').innerHTML = '<div class="alert alert-warning m-3"><strong>Configuration Required:</strong> Please set your Google Maps API key in the .env file.</div>';
    }
</script>
@endif
@endpush
