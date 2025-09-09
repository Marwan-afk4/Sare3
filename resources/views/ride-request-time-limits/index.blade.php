@extends('layouts.app')
@php
	$currentPage = 'ride-request-time-limits';
@endphp
@section('title', __('Ride Request Time Limits'))
@section('content')
<div class="container-fluid">
	<h1 class="mb-3">{{__('Ride Request Time Limits')}}</h1>
	<div class="mb-3">
		<a href="{{ route('ride-request-time-limits.create') }}" class="btn btn-primary btn-sm me-1">{{__('Create Ride Request Time Limit')}} <i class="fa fa-plus"></i></a>
	</div>
	<div class='main-card mb-3 card'>
		<div class='card-body'>
			<table class="mb-0 table table-hover">
				<tr>
					<th>
						<a href="{{ route('ride-request-time-limits.index', ['sort' => 'id', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Id") }}
							@if($sortField === 'id')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th>
						<a href="{{ route('ride-request-time-limits.index', ['sort' => 'time_limit_seconds', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Time Limit Seconds") }}
							@if($sortField === 'time_limit_seconds')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th>
						<a href="{{ route('ride-request-time-limits.index', ['sort' => 'created_at', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Created At") }}
							@if($sortField === 'created_at')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th class="text-center">{{ __('Actions') }}</th>
				</tr>
				@foreach($rideRequestTimeLimits as $rideRequestTimeLimit)
				<tr>
					<td>{{ $rideRequestTimeLimit->id }}</td>
					<td>{{ $rideRequestTimeLimit->time_limit_seconds }}</td>
					<td>{{ $rideRequestTimeLimit->created_at->diffForHumans() ?? '-' }}</td>
					<td class="text-center">
						<a href='{{ route('ride-request-time-limits.show', $rideRequestTimeLimit) }}' class="btn btn-subtle-primary btn-sm me-1">{{ __("Details") }} <i class="fa fa-eye"></i></a>
						<form action="{{ route('ride-request-time-limits.destroy', $rideRequestTimeLimit) }}" method="POST" class="d-inline"
                            onsubmit="return confirm('{{ __('Are you sure you want to delete this ride request time limit?') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-subtle-danger btn-sm">
                                {{ __('Delete') }} <i class="fa fa-trash"></i>
                            </button>
                        </form>
					</td>
				</tr>
				@endforeach
			</table>
			{{ $rideRequestTimeLimits->links('pagination::custom') }}
		</div>
	</div>
</div>
@endsection
