@extends('layouts.app')
@php
    $currentPage = 'drivers-under-monitoring';
@endphp
@section('title', __('Captains Under Monitoring'))
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="mb-1">{{ __('Captains Under Monitoring') }}</h1>
                <p class="text-muted mb-0">
                    {{ __('Captains with many ride rejections or cancellations after accepting.') }}
                </p>
            </div>
            <a href="{{ route('drivers.index') }}" class="btn btn-secondary btn-sm">
                <i class="fa fa-arrow-right me-1"></i>{{ __('Back to') }} {{ __('Drivers') }}
            </a>
        </div>

        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body">
                <form action="{{ route('drivers.under-monitoring') }}" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label fw-bold">{{ __('Search') }}</label>
                        <input type="text" name="keyword" class="form-control"
                            placeholder="{{ __('Keyword') }}..." value="{{ request('keyword') }}">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-bold">{{ __('From Date') }}</label>
                        <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-bold">{{ __('To Date') }}</label>
                        <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-bold">{{ __('Driver City') }}</label>
                        <select name="city" class="form-select">
                            <option value="">{{ __('All Cities') }}</option>
                            @foreach ($cities as $city)
                                <option value="{{ $city->id }}" @selected(request('city') == $city->id)>
                                    {{ $city->name }}
                                </option>
                            @endforeach
                            <option value="no_city" @selected(request('city') === 'no_city')>
                                {{ __('No City') }}
                            </option>
                        </select>
                    </div>

                    <div class="col-md-1">
                        <label class="form-label fw-bold">{{ __('Min Rejections') }}</label>
                        <input type="number" name="min_rejections" class="form-control" min="0"
                            value="{{ $minRejections }}">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-bold">{{ __('Min Cancel After Accept') }}</label>
                        <input type="number" name="min_cancel_after_accept" class="form-control" min="0"
                            value="{{ $minCancelAfterAccept }}">
                    </div>

                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100" title="{{ __('Apply Filter') }}">
                            <i class="fa fa-filter"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="main-card mb-3 card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="mb-0 table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>
                                    <a href="{{ route('drivers.under-monitoring', array_merge(request()->except(['sort', 'order']), ['sort' => 'id', 'order' => $sortField === 'id' && $sortOrder === 'asc' ? 'desc' : 'asc'])) }}">
                                        {{ __('Id') }}
                                        @if ($sortField === 'id')
                                            <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ route('drivers.under-monitoring', array_merge(request()->except(['sort', 'order']), ['sort' => 'name', 'order' => $sortField === 'name' && $sortOrder === 'asc' ? 'desc' : 'asc'])) }}">
                                        {{ __('Name') }}
                                        @if ($sortField === 'name')
                                            <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                                        @endif
                                    </a>
                                </th>
                                <th>{{ __('Phone') }}</th>
                                <th>{{ __('City') }}</th>
                                <th>{{ __('Zone') }}</th>
                                <th>
                                    <a href="{{ route('drivers.under-monitoring', array_merge(request()->except(['sort', 'order']), ['sort' => 'rejected_count', 'order' => $sortField === 'rejected_count' && $sortOrder === 'asc' ? 'desc' : 'asc'])) }}">
                                        {{ __('Rejections') }}
                                        @if ($sortField === 'rejected_count')
                                            <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ route('drivers.under-monitoring', array_merge(request()->except(['sort', 'order']), ['sort' => 'cancel_after_accept_count', 'order' => $sortField === 'cancel_after_accept_count' && $sortOrder === 'asc' ? 'desc' : 'asc'])) }}">
                                        {{ __('Cancel After Accept') }}
                                        @if ($sortField === 'cancel_after_accept_count')
                                            <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ route('drivers.under-monitoring', array_merge(request()->except(['sort', 'order']), ['sort' => 'ignored_count', 'order' => $sortField === 'ignored_count' && $sortOrder === 'asc' ? 'desc' : 'asc'])) }}">
                                        {{ __('Ignored') }}
                                        @if ($sortField === 'ignored_count')
                                            <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ route('drivers.under-monitoring', array_merge(request()->except(['sort', 'order']), ['sort' => 'accepted_count', 'order' => $sortField === 'accepted_count' && $sortOrder === 'asc' ? 'desc' : 'asc'])) }}">
                                        {{ __('Accepted') }}
                                        @if ($sortField === 'accepted_count')
                                            <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ route('drivers.under-monitoring', array_merge(request()->except(['sort', 'order']), ['sort' => 'problem_count', 'order' => $sortField === 'problem_count' && $sortOrder === 'asc' ? 'desc' : 'asc'])) }}">
                                        {{ __('Total Problems') }}
                                        @if ($sortField === 'problem_count')
                                            <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                                        @endif
                                    </a>
                                </th>
                                <th>{{ __('Status') }}</th>
                                <th class="text-center">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($drivers as $driver)
                                @php
                                    $problemCount = ($driver->rejected_count ?? 0) + ($driver->cancel_after_accept_count ?? 0);
                                @endphp
                                <tr>
                                    <td>{{ $driver->id }}</td>
                                    <td>{{ $driver->name ?? '-' }}</td>
                                    <td>{{ $driver->phone ?? '-' }}</td>
                                    <td>
                                        @if ($driver->city)
                                            <span class="badge bg-primary">{{ $driver->city->name }}</span>
                                        @else
                                            <span class="text-muted">{{ __('No City') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($driver->zone)
                                            <span class="badge bg-info">{{ $driver->zone->name }}</span>
                                        @else
                                            <span class="text-muted">{{ __('No Zone') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-danger">{{ $driver->rejected_count }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning text-dark">{{ $driver->cancel_after_accept_count }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $driver->ignored_count }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">{{ $driver->accepted_count }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-dark">{{ $problemCount }}</span>
                                    </td>
                                    <td>
                                        @if ($driver->status)
                                            {!! $driver->status->badge() !!}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-center text-nowrap">
                                        <a href="{{ route('drivers.show', $driver) }}" class="btn btn-sm btn-primary" title="{{ __('Details') }}">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <a href="{{ route('drivers.ride-history', $driver) }}" class="btn btn-sm btn-info" title="{{ __('Ride History') }}">
                                            <i class="fa fa-history"></i>
                                        </a>
                                        <a href="{{ route('drivers.edit', $driver) }}" class="btn btn-sm btn-warning" title="{{ __('Edit') }}">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="text-center text-muted py-4">
                                        {{ __('No captains currently match the monitoring thresholds.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($drivers->hasPages())
                    <div class="mt-3">
                        {{ $drivers->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
