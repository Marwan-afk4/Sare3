@extends('layouts.app')
@php
    $currentPage = 'rides';
@endphp
@section('title', __('Rides'))
@section('content')
    <div class="container-fluid">
        <h1 class="mb-3">{{ __('Rides') }}</h1>
        <div class="mb-3">
        <a href="{{ route('rides.index') }}" class="btn btn-primary btn-sm me-1">{{__('All')}}</a>
            @foreach ($rideStatuses as $status)
                <a href="{{ route('rides.index', ['status' => $status->value]) }}" class="btn" style="background-color: #{{ $status->color() }}; color: #{{ $status->textColor() }}">
                    {{ $status->label() }}
                    ({{ $ridesStatusCounts[$status->value]?? '0' }})
                </a>
            @endforeach
        </div>

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
                                href="{{ route('rides.index', ['sort' => 'estimated_km', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
                                {{ __('Estimated Km') }}
                                @if ($sortField === 'estimated_km')
                                    <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a
                                href="{{ route('rides.index', ['sort' => 'estimated_time', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
                                {{ __('Estimated Time') }}
                                @if ($sortField === 'estimated_time')
                                    <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                                @endif
                            </a>
                        </th>
                        <th>
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
                        </th>
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
                                href="{{ route('rides.index', ['sort' => 'status', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
                                {{ __('Status') }}
                                @if ($sortField === 'status')
                                    <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                                @endif
                            </a>
                        </th>
                        <th class="text-center">{{ __('Actions') }}</th>
                    </tr>
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
                            </td>
                            <td>
                                @if ($ride->carCategory?->name)
                                    <a
                                        href="{{ route('car-categories.show', $ride->carCategory) }}">{{ $ride->carCategory->name }}</a>
                                @endif
                            </td>
                            <td>{{ $ride->pickup_address }}</td>
                            <td>{{ $ride->dropoff_address }}</td>
                            <td>{{ $ride->estimated_km }}</td>
                            <td>{{ $ride->estimated_time }}</td>
                            <td>{{ $ride->calculated_initial_price }}</td>
                            <td>{{ $ride->calculated_final_price }}</td>
                            <td>{{ $ride->time_taken }}</td>
                            <td>{!! $ride->status->badge() !!}</td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <a href='{{ route('rides.show', $ride) }}'
                                        class="btn btn-subtle-primary btn-sm" title="{{ __('View Details') }}">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    @if($ride->pickup_lat && $ride->pickup_lng)
                                        <a href='{{ route('rides.track', $ride) }}'
                                            class="btn btn-subtle-success btn-sm" title="{{ __('Track Ride') }}">
                                            @if(in_array($ride->status->value, ['in_progress', 'accepted', 'waiting_user']))
                                                <i class="fa fa-location-arrow"></i>
                                            @else
                                                <i class="fa fa-route"></i>
                                            @endif
                                        </a>
                                    @endif
                                </div>
                                {{-- <a href='{{ route('rides.edit', $ride) }}' class="btn btn-subtle-warning btn-sm me-1">{{ __("Edit") }} <i class="fa fa-edit"></i></a> --}}
                                {{-- <form method='POST' action='{{ route('rides.destroy', $ride) }}' onsubmit='return confirm("Are you sure you want to delete this item?")'>
							<input type='hidden' name='_method' value='DELETE'>
							<button type='submit' class="btn btn-square btn-danger">{{ __('Delete') }}</button>
						</form> --}}
                            </td>
                        </tr>
                    @endforeach
                </table>
                {{ $rides->links('pagination::custom') }}
            </div>
        </div>
    </div>
@endsection
