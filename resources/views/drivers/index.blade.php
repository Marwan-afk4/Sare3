@extends('layouts.app')
@php
    $currentPage = 'drivers';
@endphp
@section('title', __('Drivers'))
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">{{ __('Drivers') }}</h1>
            <a href="{{ route('drivers.create') }}" class="btn btn-primary">
                <i class="fa fa-plus me-2"></i>{{ __('Create Driver') }}
            </a>
        </div>

        {{-- Activity Filter Buttons --}}
        <div class="mb-3">
            <label class="form-label fw-bold">{{ __('Filter by Activity') }}</label>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('drivers.index') }}" class="btn btn-outline-primary btn-sm">
                    {{ __('All') }}
                </a>
                @foreach ($driverActivtyStatus as $activity)
                    <a href="{{ route('drivers.index', ['activity' => $activity->value]) }}" class="btn btn-sm"
                        style="background-color: #{{ $activity->color() }}; color: #{{ $activity->textColor() }}">
                        {{ $activity->label() }} ({{ $driverActivityCounts[$activity->value] ?? '0' }})
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Zone Filter Buttons --}}
        <div class="mb-3">
            <label class="form-label fw-bold">{{ __('Filter by Zone') }}</label>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('drivers.index') }}" class="btn btn-outline-info btn-sm">
                    {{ __('All Zones') }}
                </a>
                @foreach ($zones as $zone)
                    <a href="{{ route('drivers.index', ['zone' => $zone->id]) }}"
                        class="btn btn-sm {{ request('zone') == $zone->id ? 'btn-info' : 'btn-outline-info' }}">
                        {{ $zone->name }} ({{ $zone->driver_count }})
                    </a>
                @endforeach
                <a href="{{ route('drivers.index', ['zone' => 'no_zone']) }}"
                    class="btn btn-sm {{ request('zone') === 'no_zone' ? 'btn-warning' : 'btn-outline-warning' }}">
                    {{ __('No Zone') }} ({{ $driversWithNoZoneCount }})
                </a>
            </div>
        </div>

        {{-- Search Form --}}
        <div class="d-flex justify-content-end">
            <form action="{{ route(Route::currentRouteName(), [], false) }}" method="GET" class="d-flex"
                style="max-width: 300px;">
                @if (request('keyword'))
                    <a class="btn btn-outline-secondary me-1"
                        href="{{ route(Route::currentRouteName(), [], false) }}">
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
                            href="{{ route('drivers.index', ['sort' => 'id', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
                            {{ __('Id') }}
                            @if ($sortField === 'id')
                                <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                            @endif
                        </a>
                    </th>
                    <th>
                        <a
                            href="{{ route('drivers.index', ['sort' => 'name', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
                            {{ __('Name') }}
                            @if ($sortField === 'name')
                                <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                            @endif
                        </a>
                    </th>
                    <th>
                        <a
                            href="{{ route('drivers.index', ['sort' => 'email', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
                            {{ __('Email') }}
                            @if ($sortField === 'email')
                                <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                            @endif
                        </a>
                    </th>
                    <th>
                        <a
                            href="{{ route('drivers.index', ['sort' => 'phone', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
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
                            href="{{ route('drivers.index', ['sort' => 'wallet', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
                            {{ __('Wallet') }}
                            @if ($sortField === 'wallet')
                                <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                            @endif
                        </a>
                    </th>
                    <th>{{ __('Zone') }}</th>
                    <th>
						<a href="{{ route('drivers.index', ['sort' => 'activity', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Activity") }}
							@if ($sortField === 'activity')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
                    <th>
                        <a
                            href="{{ route('drivers.index', ['sort' => 'status', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
                            {{ __('Status') }}
                            @if ($sortField === 'status')
                                <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                            @endif
                        </a>
                    </th>
                    <th>
                        <a
                            href="{{ route('drivers.index', ['sort' => 'created_at', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
                            {{ __('Created At') }}
                            @if ($sortField === 'created_at')
                                <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                            @endif
                        </a>
                    </th>
                    <th class="text-center">{{ __('Actions') }}</th>
                </tr>
                @foreach ($drivers as $driver)
                    <tr>
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
                        <td>{!! $driver->activity->badge() !!} </td>
                        <td>{!! $driver->status->badge() !!} </td>
                        {{-- <td>{{ $user->role }}</td> --}}
                        <td>{{ $driver->created_at ? $driver->created_at->diffForHumans() : '-' }}</td>
                        <td class="text-center">
                            <a href='{{ route('drivers.show', $driver) }}'
                                class="btn btn-subtle-primary btn-sm me-1">{{ __('Details') }} <i
                                    class="fa fa-eye"></i></a>
                            {{-- <a href='{{ route('drivers.edit', $driver) }}'
                                    class="btn btn-subtle-warning btn-sm me-1">{{ __('Edit') }} <i
                                        class="fa fa-edit"></i></a> --}}
                            {{-- <form method='POST' action='{{ route('users.destroy', $user) }}' onsubmit='return confirm("Are you sure you want to delete this item?")'>
							<input type='hidden' name='_method' value='DELETE'>
							<button type='submit' class="btn btn-square btn-danger">{{ __('Delete') }}</button>
						</form> --}}
                        </td>
                    </tr>
                @endforeach
            </table>
            {{ $drivers->links('pagination::custom') }}
        </div>
    </div>
@endsection
