@extends('layouts.app')
@php
    $currentPage = 'rides';
@endphp
@section('title', __('Rides'))
@section('content')
    <div class="container-fluid">
        <h1 class="mb-3">{{ __('Rides') }}</h1>

        {{-- Filters Section --}}
        <div class="card mb-3">
            <div class="card-body">
                <form action="{{ route('rides.index') }}" method="GET" id="filterForm">
                    <div class="row g-3 align-items-end">
                        {{-- Keyword Search --}}
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Search') }}</label>
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
                            <label class="form-label">{{ __('Min KM') }}</label>
                            <input type="number" name="min_km" class="form-control" step="0.1" min="0"
                                placeholder="{{ __('Min') }}" value="{{ request('min_km') }}">
                        </div>

                        {{-- Max KM Filter --}}
                        <div class="col-md-2">
                            <label class="form-label">{{ __('Max KM') }}</label>
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

                    {{-- Offer-history filters (requirements 12 & 13) --}}
                    <div class="row g-3 align-items-end mt-1">
                        <div class="col-md-3">
                            <label class="form-label">{{ __('Accepted after (seconds)') }}</label>
                            <input type="number" name="accepted_after_seconds" min="0" step="1"
                                class="form-control" placeholder="e.g. 60"
                                value="{{ request('accepted_after_seconds') }}">
                            <small class="text-muted">{{ __('Rides whose accepting captain took at least N seconds.') }}</small>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">{{ __('Min captains offered') }}</label>
                            <input type="number" name="min_offers" min="1" step="1"
                                class="form-control" placeholder="e.g. 3"
                                value="{{ request('min_offers') }}">
                            <small class="text-muted">{{ __('Rides that cycled through at least N captains.') }}</small>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">{{ __('Cancelled before any accept') }}</label>
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

        <!-- Status Filters -->
        <div class="mb-3">
            <label class="form-label fw-bold">{{ __('Filter by Status') }}</label>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('rides.index', request()->except('status')) }}" 
                   class="btn btn-primary btn-sm {{ !request('status') ? 'active' : '' }}">
                    {{ __('All') }}
                </a>
                @foreach ($rideStatuses as $status)
                    <a href="{{ route('rides.index', array_merge(request()->except('status'), ['status' => $status->value])) }}" 
                       class="btn btn-sm {{ request('status') === $status->value ? 'active' : '' }}"
                        style="background-color: #{{ $status->color() }}; color: #{{ $status->textColor() }}">
                        {{ $status->label() }}
                        ({{ $ridesStatusCounts[$status->value] ?? '0' }})
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Rides Table -->
        <div class='main-card mb-3 card'>
            <div class='card-body'>
                <table class="mb-0 table table-hover">
                    <tr>
                        <th>
                            <a
                                href="{{ route('rides.index', ['sort' => 'id', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
                                {{ __('Id') }}
                                @if ($sortField === 'id')
                                    <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a
                                href="{{ route('rides.index', ['sort' => 'user_id', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
                                {{ __('User') }}
                                @if ($sortField === 'user_id')
                                    <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a
                                href="{{ route('rides.index', ['sort' => 'driver_id', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
                                {{ __('Driver') }}
                                @if ($sortField === 'driver_id')
                                    <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a
                                href="{{ route('rides.index', ['sort' => 'car_category_id', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
                                {{ __('Car Category') }}
                                @if ($sortField === 'car_category_id')
                                    <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a
                                href="{{ route('rides.index', ['sort' => 'pickup_address', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
                                {{ __('Pickup Address') }}
                                @if ($sortField === 'pickup_address')
                                    <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a
                                href="{{ route('rides.index', ['sort' => 'dropoff_address', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
                                {{ __('Dropoff Address') }}
                                @if ($sortField === 'dropoff_address')
                                    <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a
                                href="{{ route('rides.index', ['sort' => 'total_distance_in_km', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
                                {{ __('Total Distance') }}
                                @if ($sortField === 'estimated_km')
                                    <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                                @endif
                            </a>
                        </th>
                        {{-- <th>
                            <a
                                href="{{ route('rides.index', ['sort' => 'estimated_time', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
                                {{ __('Estimated Time') }}
                                @if ($sortField === 'estimated_time')
                                    <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                                @endif
                            </a>
                        </th> --}}
                        {{-- <th>
                            <a
                                href="{{ route('rides.index', ['sort' => 'calculated_initial_price', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
                                {{ __('Initial Price') }}
                                @if ($sortField === 'calculated_initial_price')
                                    <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a
                                href="{{ route('rides.index', ['sort' => 'calculated_final_price', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
                                {{ __('Final Price') }}
                                @if ($sortField === 'calculated_final_price')
                                    <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                                @endif
                            </a>
                        </th> --}}
                        <th>
                            <a
                                href="{{ route('rides.index', ['sort' => 'time_taken', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
                                {{ __('Time Taken') }}
                                @if ($sortField === 'time_taken')
                                    <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a
                                href="{{ route('rides.index', ['sort' => 'calculated_final_price', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
                                {{ __('Fare') }}
                                @if ($sortField === 'calculated_final_price')
                                    <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a
                                href="{{ route('rides.index', ['sort' => 'status', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
                                {{ __('Status') }}
                                @if ($sortField === 'status')
                                    <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a
                                href="{{ route('rides.index', ['sort' => 'created_at', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
                                {{ __('Created At') }}
                                @if ($sortField === 'created_at')
                                    <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                                @endif
                            </a>
                        </th>
                        <th class="text-center">{{ __('Actions') }}</th>
                    </tr>
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
                                    @if ($ride->cancelled_before_accept)
                                        <div>
                                            <span class="badge bg-dark" title="{{ __('Passenger cancelled before any captain accepted') }}">
                                                <i class="fa fa-ban"></i> {{ __('Cancelled before accept') }}
                                            </span>
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
                                <td>{{ $ride->total_distance_in_km }}</td>
                                {{-- <td>{{ $ride->estimated_time }}</td> --}}
                                {{-- <td>{{ $ride->calculated_initial_price }}</td>
                                <td>{{ $ride->calculated_final_price }}</td> --}}
                                <td>{{ $ride->time_taken }}</td>
                                <td>
                                    @if ($ride->coupon_id && $ride->calculated_initial_price && $ride->calculated_final_price)
                                        <div>
                                            <span class="text-muted">{{ __('Before') }}:</span>
                                            <strong>${{ number_format($ride->calculated_initial_price, 2) }}</strong>
                                            <span class="text-muted">→</span>
                                            <span class="text-muted">{{ __('After') }}:</span>
                                            <strong class="text-success">${{ number_format($ride->calculated_final_price, 2) }}</strong>
                                            <br>
                                            <small class="text-info">
                                                <i class="fa fa-tag"></i> {{ $ride->coupon->code ?? __('Coupon Applied') }}
                                                (${{ number_format($ride->coupon_discount ?? 0, 2) }})
                                            </small>
                                        </div>
                                    @elseif ($ride->calculated_final_price)
                                        <strong>${{ number_format($ride->calculated_final_price, 2) }}</strong>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{!! $ride->status->badge() !!}</td>
                                <td>{{ $ride->created_at?->translatedFormat('l d F Y - h:i A') ?? '' }}</td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <a href='{{ route('rides.show', $ride) }}' class="btn btn-subtle-primary btn-sm"
                                            title="{{ __('View Details') }}">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        @if ($ride->pickup_lat && $ride->pickup_lng)
                                            <a href='{{ route('rides.track', $ride) }}'
                                                class="btn btn-subtle-success btn-sm" title="{{ __('Track Ride') }}">
                                                @if (in_array($ride->status->value, ['in_progress', 'accepted', 'waiting_user']))
                                                    <i class="fa fa-location-arrow"></i>
                                                @else
                                                    <i class="fa fa-route"></i>
                                                @endif
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="mt-3">
                    {{ $rides->appends(request()->query())->links('pagination::custom') }}
                </div>
            </div>
        </div>
    </div>
@endsection
