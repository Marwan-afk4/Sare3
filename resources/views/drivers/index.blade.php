@extends('layouts.app')
@php
    $currentPage = 'drivers';
@endphp
@section('title', __('Drivers'))
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">{{ __('Drivers') }}</h1>
            <div class="d-flex gap-2">
                <a href="{{ route('drivers.export', request()->query()) }}" class="btn btn-success">
                    <i class="fa fa-file-excel me-2"></i>{{ __('Export to Excel') }}
                </a>
                <a href="{{ route('drivers.export-phones', request()->query()) }}" class="btn btn-outline-success">
                    <i class="fa fa-phone me-2"></i>{{ __('Export Phone Numbers') }}
                </a>
                <a href="{{ route('drivers.create') }}" class="btn btn-primary">
                    <i class="fa fa-plus me-2"></i>{{ __('Create Driver') }}
                </a>
            </div>
        </div>

        {{-- Activity Filter Buttons --}}
        <div class="mb-3">
            <label class="form-label fw-bold">{{ __('Filter by Activity') }}</label>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('drivers.index', request()->except('activity')) }}" class="btn btn-outline-primary btn-sm">
                    {{ __('All') }}
                </a>
                @foreach ($driverActivtyStatus as $activity)
                    <a href="{{ route('drivers.index', array_merge(request()->except('activity'), ['activity' => $activity->value])) }}" class="btn btn-sm"
                        style="background-color: #{{ $activity->color() }}; color: #{{ $activity->textColor() }}">
                        {{ $activity->label() }} ({{ $driverActivityCounts[$activity->value] ?? '0' }})
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Status Filter Buttons --}}
        <div class="mb-3">
            <label class="form-label fw-bold">{{ __('Filter by Status') }}</label>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('drivers.index', request()->except('status')) }}" class="btn btn-outline-secondary btn-sm">
                    {{ __('All') }}
                </a>
                @foreach ($driverStatusCases as $driverStatus)
                    <a href="{{ route('drivers.index', array_merge(request()->except('status'), ['status' => $driverStatus->value])) }}" class="btn btn-sm {{ request('status') === $driverStatus->value ? '' : 'btn-outline-secondary' }}"
                        style="{{ request('status') === $driverStatus->value ? 'background-color: #' . $driverStatus->color() . '; color: #' . $driverStatus->textColor() : '' }}">
                        {{ $driverStatus->label() }} ({{ $driverStatusCounts[$driverStatus->value] ?? '0' }})
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Availability Filter Buttons --}}
        <div class="mb-3">
            <label class="form-label fw-bold">{{ __('Filter by Availability') }}</label>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('drivers.index', request()->except('availability')) }}" class="btn btn-outline-dark btn-sm">
                    {{ __('All') }}
                </a>
                <a href="{{ route('drivers.index', array_merge(request()->except('availability'), ['availability' => '1'])) }}"
                    class="btn btn-sm {{ request('availability') === '1' ? 'btn-success' : 'btn-outline-success' }}">
                    {{ __('الحالة: متصل ومتاح') }} 🟢 ({{ $onlineDriversCount }})
                </a>
                <a href="{{ route('drivers.index', array_merge(request()->except('availability'), ['availability' => '0'])) }}"
                    class="btn btn-sm {{ request('availability') === '0' ? 'btn-secondary' : 'btn-outline-secondary' }}">
                    {{ __('غير متصل') }} ⚫ ({{ $offlineDriversCount }})
                </a>
            </div>
        </div>

        {{-- Zone Filter Buttons --}}
        <div class="mb-3">
            <label class="form-label fw-bold">{{ __('Filter by Zone') }}</label>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('drivers.index', request()->except('zone')) }}" class="btn btn-outline-info btn-sm">
                    {{ __('All Zones') }}
                </a>
                @foreach ($zones as $zone)
                    <a href="{{ route('drivers.index', array_merge(request()->except('zone'), ['zone' => $zone->id])) }}"
                        class="btn btn-sm {{ request('zone') == $zone->id ? 'btn-info' : 'btn-outline-info' }}">
                        {{ $zone->name }} ({{ $zone->driver_count }})
                    </a>
                @endforeach
                <a href="{{ route('drivers.index', array_merge(request()->except('zone'), ['zone' => 'no_zone'])) }}"
                    class="btn btn-sm {{ request('zone') === 'no_zone' ? 'btn-warning' : 'btn-outline-warning' }}">
                    {{ __('No Zone') }} ({{ $driversWithNoZoneCount }})
                </a>
            </div>
        </div>

        {{-- City Filter Buttons --}}
        <div class="mb-3">
            <label class="form-label fw-bold">{{ __('Filter by City') }}</label>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('drivers.index', request()->except('city')) }}" class="btn btn-outline-primary btn-sm">
                    {{ __('All Cities') }}
                </a>
                @foreach ($cities as $city)
                    @if ($city->driver_count > 0)
                        <a href="{{ route('drivers.index', array_merge(request()->except('city'), ['city' => $city->id])) }}"
                            class="btn btn-sm {{ request('city') == $city->id ? 'btn-primary' : 'btn-outline-primary' }}">
                            {{ $city->name }} ({{ $city->driver_count }})
                        </a>
                    @endif
                @endforeach
                <a href="{{ route('drivers.index', array_merge(request()->except('city'), ['city' => 'no_city'])) }}"
                    class="btn btn-sm {{ request('city') === 'no_city' ? 'btn-warning' : 'btn-outline-warning' }}">
                    {{ __('No City') }} ({{ $driversWithNoCityCount }})
                </a>
            </div>
        </div>

    {{-- Car Year Filter Buttons --}}
    <div class="mb-3">
        <label class="form-label fw-bold">{{ __('Filter by Car Year') }}</label>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('drivers.index', request()->except('car_year')) }}" class="btn btn-outline-success btn-sm">
                {{ __('All Years') }}
            </a>
            @foreach ($carYears as $year)
                <a href="{{ route('drivers.index', array_merge(request()->except('car_year'), ['car_year' => $year])) }}"
                    class="btn btn-sm {{ request('car_year') == $year ? 'btn-success' : 'btn-outline-success' }}">
                    {{ $year }} ({{ $carYearCounts[$year] ?? 0 }})
                </a>
            @endforeach
        </div>
    </div>

        {{-- Wallet Balance Filter --}}
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('drivers.index') }}" class="row g-3 align-items-end">
                    @foreach (request()->except(['balance_operator', 'balance_amount', 'page']) as $key => $value)
                        @if (is_array($value))
                            @foreach ($value as $item)
                                <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                            @endforeach
                        @else
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach
                    <div class="col-md-4">
                        <label for="balance_operator" class="form-label fw-bold">{{ __('Balance Condition') }}</label>
                        <select name="balance_operator" id="balance_operator" class="form-select">
                            <option value="">{{ __('All Balances') }}</option>
                            <option value="lt" {{ ($balanceOperator ?? '') === 'lt' ? 'selected' : '' }}>{{ __('Less than') }}</option>
                            <option value="eq" {{ ($balanceOperator ?? '') === 'eq' ? 'selected' : '' }}>{{ __('Equal to') }}</option>
                            <option value="gt" {{ ($balanceOperator ?? '') === 'gt' ? 'selected' : '' }}>{{ __('Greater than') }}</option>
                            <option value="lte" {{ ($balanceOperator ?? '') === 'lte' ? 'selected' : '' }}>{{ __('Less than or equal to') }}</option>
                            <option value="gte" {{ ($balanceOperator ?? '') === 'gte' ? 'selected' : '' }}>{{ __('Greater than or equal to') }}</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="balance_amount" class="form-label fw-bold">{{ __('Balance Amount (JOD)') }}</label>
                        <input type="number" name="balance_amount" id="balance_amount" class="form-control"
                            step="0.001" min="0" placeholder="10"
                            value="{{ $balanceAmount ?? '' }}">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fa fa-filter me-1"></i> {{ __('Apply Filters') }}
                        </button>
                        @if (request()->filled('balance_operator') || request()->filled('balance_amount'))
                            <a href="{{ route('drivers.index', request()->except(['balance_operator', 'balance_amount', 'page'])) }}"
                               class="btn btn-outline-secondary w-100 mt-2">
                                <i class="fa fa-times me-1"></i> {{ __('Clear Filters') }}
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        {{-- Search Form --}}
        <div class="d-flex justify-content-end">
            <form action="{{ route(Route::currentRouteName(), [], false) }}" method="GET" class="d-flex"
                style="max-width: 300px;">
                @foreach (request()->except(['keyword', 'page']) as $key => $value)
                    @if (is_array($value))
                        @foreach ($value as $item)
                            <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                        @endforeach
                    @else
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endif
                @endforeach
                @if (request('keyword'))
                    <a class="btn btn-outline-secondary me-1"
                        href="{{ route(Route::currentRouteName(), request()->except('keyword')) }}">
                        <i class="fa fa-times"></i>
                    </a>
                @endif
                <input type="text" name="keyword" class="form-control form-control-sm" autocomplete="off"
                    placeholder="{{ __('Keyword') }}..." value="{{ request('keyword') }}">
                <button type="submit" class="btn btn-sm btn-primary ms-1">
                    <i class="fa fa-search"></i>
                </button>
            </form>
        </div>
    </div>

    <div class='main-card mb-3 card'>
        <div class='card-body'>
            <table class="mb-0 table table-hover">
                <tr>
                    <th>
                        <a
                            href="{{ route('drivers.index', array_merge(request()->except(['sort', 'order']), ['sort' => 'id', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc'])) }}">
                            {{ __('Id') }}
                            @if ($sortField === 'id')
                                <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                            @endif
                        </a>
                    </th>
                    <th>
                        <a
                            href="{{ route('drivers.index', array_merge(request()->except(['sort', 'order']), ['sort' => 'name', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc'])) }}">
                            {{ __('Name') }}
                            @if ($sortField === 'name')
                                <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                            @endif
                        </a>
                    </th>
                    <th>
                        <a
                            href="{{ route('drivers.index', array_merge(request()->except(['sort', 'order']), ['sort' => 'email', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc'])) }}">
                            {{ __('Email') }}
                            @if ($sortField === 'email')
                                <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                            @endif
                        </a>
                    </th>
                    <th>
                        <a
                            href="{{ route('drivers.index', array_merge(request()->except(['sort', 'order']), ['sort' => 'phone', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc'])) }}">
                            {{ __('Phone') }}
                            @if ($sortField === 'phone')
                                <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                            @endif
                        </a>
                    </th>
                    {{-- <th>
						<a href="{{ route('drivers.index', ['sort' => 'image', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Image") }}
							@if ($sortField === 'image')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th> --}}
                    <th>
                        <a
                            href="{{ route('drivers.index', array_merge(request()->except(['sort', 'order']), ['sort' => 'wallet', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc'])) }}">
                            {{ __('Wallet') }}
                            @if ($sortField === 'wallet')
                                <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                            @endif
                        </a>
                    </th>
                    <th>{{ __('Zone') }}</th>
                    <th>{{ __('City') }}</th>
                    <th>{{ __('Availability') }}</th>
                    <th>
						<a href="{{ route('drivers.index', array_merge(request()->except(['sort', 'order']), ['sort' => 'activity', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc'])) }}">
							{{ __("Activity") }}
							@if ($sortField === 'activity')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
                    <th>
                        <a
                            href="{{ route('drivers.index', array_merge(request()->except(['sort', 'order']), ['sort' => 'status', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc'])) }}">
                            {{ __('Status') }}
                            @if ($sortField === 'status')
                                <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                            @endif
                        </a>
                    </th>
                    <th>
                        <a
                            href="{{ route('drivers.index', array_merge(request()->except(['sort', 'order']), ['sort' => 'created_at', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc'])) }}">
                            {{ __('Created At') }}
                            @if ($sortField === 'created_at')
                                <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                            @endif
                        </a>
                    </th>
                    <th class="text-center">{{ __('Actions') }}</th>
                </tr>
                @foreach ($drivers as $driver)
                    <tr class="{{ $driver->status->value === 'pending' ? 'driver-row-pending' : '' }}">
                        <td>{{ $driver->id }}</td>
                        <td>{{ $driver->name ?? '-' }}</td>
                        <td>{{ $driver->email ?? '-' }}</td>
                        <td>{{ $driver->phone ?? '-' }}</td>
                        {{-- <td>{{ $driver->image }}</td> --}}
                        <td>
                            @if($driver->wallet < 0)
                                <span style="color: red;">{{ $driver->wallet }} 🔴</span>
                            @else
                                {{ $driver->wallet ?? '-' }}
                            @endif
                        </td>
                        <td>
                            @if($driver->zone)
                                <span class="badge bg-info">{{ $driver->zone->name }}</span>
                            @else
                                <span class="text-muted">{{ __('No Zone') }}</span>
                            @endif
                        </td>
                        <td>
                            @if($driver->city)
                                <span class="badge bg-primary">{{ $driver->city->name }}</span>
                            @else
                                <span class="text-muted">{{ __('No City') }}</span>
                            @endif
                        </td>
                        <td>
                            @if($driver->is_available)
                                <span class="badge bg-success">{{ __('الحالة: متصل ومتاح') }} 🟢</span>
                            @else
                                <span class="badge bg-secondary">{{ __('Offline') }} ⚫</span>
                            @endif
                        </td>
                        <td>
                            @if($driver->activity)
                                {!! $driver->activity->badge() !!}
                            @else
                                -
                            @endif
                        </td>
                        <td>{!! $driver->status->badge() !!} </td>
                        {{-- <td>{{ $user->role }}</td> --}}
                        <td>{{ $driver->created_at ? $driver->created_at->diffForHumans() : '-' }}</td>
                        <td class="text-center">
                            <a href='{{ route('drivers.show', $driver) }}'
                                class="btn btn-subtle-primary btn-sm me-1">{{ __('Details') }} <i
                                    class="fa fa-eye"></i></a>
                            <a href='{{ route('drivers.edit', $driver) }}'
                                class="btn btn-subtle-warning btn-sm me-1">{{ __('Edit') }} <i
                                    class="fa fa-edit"></i></a>
                            <form method="POST" action="{{ route('drivers.destroy', $driver) }}" class="d-inline" onsubmit="return confirm('{{ __('Are you sure you want to delete this driver and all their data? This cannot be undone.') }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">{{ __('Delete') }} <i class="fa fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </table>
            {{ $drivers->links('pagination::custom') }}
        </div>
    </div>
@endsection

@push('styles')
<style>
    /* Pending driver row: soft amber highlight */
    tr.driver-row-pending {
        background-color: rgba(251, 191, 36, 0.12) !important;
        border-left: 3px solid #FBBF24;
        transition: background-color 0.3s ease;
    }
    tr.driver-row-pending:hover {
        background-color: rgba(251, 191, 36, 0.22) !important;
    }
    tr.driver-row-pending td:first-child {
        position: relative;
    }
</style>
@endpush
