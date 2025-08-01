@extends('layouts.app')
@php
	$currentPage = 'ride-request-time-limits';
@endphp
@section('title', $rideRequestTimeLimit->name)
@section('content')
<div class="container-fluid">
	<h1>{{ $rideRequestTimeLimit->id }}</h1>
	<div class="mb-3">
		<a href="{{ route('ride-request-time-limits.index') }}" class="btn btn-secondary btn-sm me-1"> <i class="fa fa-arrow-right"></i> {{__('Back to')}} {{__('Ride Request Time Limits')}}</a>
		{{-- <a href='{{ route('ride-request-time-limits.edit', $rideRequestTimeLimit) }}' class="btn btn-warning btn-sm me-1">{{ __('Edit') }} <i class="fa fa-edit"></i></a> --}}
	</div>
	<div class="card">
		<div class="card-body">
			<ul class="list-group list-group-flush">
				<li class="list-group-item">
					<strong>{{ __("Id") }}:</strong> {{ $rideRequestTimeLimit->id }}
				</li>
				<li class="list-group-item">
					<strong>{{ __("Time Limit Seconds") }}:</strong> {{ $rideRequestTimeLimit->time_limit_seconds }}
				</li>
				<li class="list-group-item">
					<strong>{{ __("Created At") }}:</strong> {{ $rideRequestTimeLimit->created_at->diffForHumans() ?? '-' }}
				</li>
				<li class="list-group-item">
					<strong>{{ __("Updated At") }}:</strong> {{ $rideRequestTimeLimit->updated_at->diffForHumans() ?? '-' }}
				</li>
			</ul>
		</div>
        <div class="card-footer">
            <a wire:navigate href='{{ route('ride-request-time-limits.edit', $rideRequestTimeLimit) }}' class="btn btn-warning btn-sm me-1">{{ __('Edit') }} <i class="fa fa-edit"></i></a>
        </div>
	</div>
</div>
@endsection
