@extends('layouts.app')
@php
	$currentPage = 'cancelation-rides';
@endphp
@section('title', __('Cancelation Rides'))
@section('content')
<div class="container-fluid">
	<h1 class="mb-3">{{__('Cancelation Rides')}}</h1>
	{{-- <div class="mb-3">
		<a href="{{ route('cancelation-rides.create') }}" class="btn btn-primary btn-sm me-1">{{__('Create Cancelation Ride')}} <i class="fa fa-plus"></i></a>
	</div> --}}
	<div class='main-card mb-3 card'>
		<div class='card-body'>
			<table class="mb-0 table table-hover">
				<tr>
					<th>
						<a href="{{ route('cancelation-rides.index', ['sort' => 'id', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Id") }}
							@if($sortField === 'id')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th>
						<a href="{{ route('cancelation-rides.index', ['sort' => 'ride_id', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Ride") }}
							@if($sortField === 'ride_id')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th>
						<a href="{{ route('cancelation-rides.index', ['sort' => 'user_id', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("User") }}
							@if($sortField === 'user_id')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th>
						<a href="{{ route('cancelation-rides.index', ['sort' => 'driver_id', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Driver") }}
							@if($sortField === 'driver_id')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th>
						<a href="{{ route('cancelation-rides.index', ['sort' => 'cancelation_policy_id', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Cancelation Policy") }}
							@if($sortField === 'cancelation_policy_id')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th>
						<a href="{{ route('cancelation-rides.index', ['sort' => 'canceled_by', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Canceled By") }}
							@if($sortField === 'canceled_by')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th>
						<a href="{{ route('cancelation-rides.index', ['sort' => 'penalty_applied', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Penalty Applied") }}
							@if($sortField === 'penalty_applied')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th>
						<a href="{{ route('cancelation-rides.index', ['sort' => 'penalty_amount', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Penalty Amount") }}
							@if($sortField === 'penalty_amount')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th>
						<a href="{{ route('cancelation-rides.index', ['sort' => 'canceled_at', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Canceled At") }}
							@if($sortField === 'canceled_at')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					{{-- <th>
						<a href="{{ route('cancelation-rides.index', ['sort' => 'reason', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Reason") }}
							@if($sortField === 'reason')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th> --}}
					<th>
						<a href="{{ route('cancelation-rides.index', ['sort' => 'created_at', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Created At") }}
							@if($sortField === 'created_at')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th class="text-center">{{ __('Actions') }}</th>
				</tr>
				@foreach($cancelationRides as $cancelationRide)
				<tr>
					<td>{{ $cancelationRide->id }}</td>
					<td>
                        @if($cancelationRide->ride)
                            <a href="{{ route('rides.show', $cancelationRide->ride) }}">{{ $cancelationRide->ride?->id }}</a>
                        @endif
                    </td>
					<td>
                        @if($cancelationRide->user)
                            <a href="{{ route('users.show', $cancelationRide->user) }}">{{ $cancelationRide->user?->name }}</a>
                        @endif
                    </td>
					<td>
                        @if($cancelationRide->driver)
                            <a href="{{ route('drivers.show', $cancelationRide->driver) }}">{{ $cancelationRide->driver?->name }}</a>
                        @endif
                    </td>
					<td>
                        @if ($cancelationRide->cancelationPolicy)
                            <a href="{{ route('cancellation-policies.show', $cancelationRide->cancelationPolicy) }}">{{ $cancelationRide->cancelationPolicy?->name }}</a>
                        @endif
                    </td>
                    <td>{{ $cancelationRide->canceled_by ? __('Driver') : __('User') }}</td>
					<td>{{ $cancelationRide->penalty_applied ? __('Yes') : __('No') }}</td>
					<td>{{ $cancelationRide->penalty_amount }}</td>
					<td>{{ $cancelationRide->canceled_at?->diffForHumans() ?? '-' }}</td>
					{{-- <td>{{ $cancelationRide->reason }}</td> --}}
					<td>{{ $cancelationRide->created_at->diffForHumans() ?? '-' }}</td>
					<td class="text-center">
						<a href='{{ route('cancelation-rides.show', $cancelationRide) }}' class="btn btn-subtle-primary btn-sm me-1">{{ __("Details") }} <i class="fa fa-eye"></i></a>
						{{-- <a href='{{ route('cancelation-rides.edit', $cancelationRide) }}' class="btn btn-subtle-warning btn-sm me-1">{{ __("Edit") }} <i class="fa fa-edit"></i></a> --}}
						{{-- <form method='POST' action='{{ route('cancelation-rides.destroy', $cancelationRide) }}' onsubmit='return confirm("Are you sure you want to delete this item?")'>
							<input type='hidden' name='_method' value='DELETE'>
							<button type='submit' class="btn btn-square btn-danger">{{ __('Delete') }}</button>
						</form> --}}
					</td>
				</tr>
				@endforeach
			</table>
			{{ $cancelationRides->links('pagination::custom') }}
		</div>
	</div>
</div>
@endsection
