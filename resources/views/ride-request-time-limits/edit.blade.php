@extends('layouts.app')
@php
	$currentPage = 'ride-request-time-limits';
@endphp
@section('title', __('Edit Ride Request Time Limit'))
@section('content')
<div class="container">
	<h1>{{ __('Edit Ride Request Time Limit') }}</h1>
	<div class="mb-3">
		<a href="{{ route('ride-request-time-limits.index') }}" class="btn btn-secondary btn-sm me-1"> <i class="fa fa-arrow-right"></i> {{__('Back to')}} {{__('Ride Request Time Limits')}}</a>
		<a href='{{ route('ride-request-time-limits.show', $rideRequestTimeLimit) }}' class="btn btn-primary btn-sm me-1">{{ __("Details") }} <i class="fa fa-eye"></i></a>
	</div>
	<div class="main-card mb-3 card">
		<div class="card-body">
			<form method='POST' action='{{ route('ride-request-time-limits.update', $rideRequestTimeLimit->id) }}' class="needs-validation" novalidate>
				@csrf
				@method('PUT')
				<x-form-input 
					name="time_limit_seconds"
					type="text"
					label="{{__('Time Limit Seconds')}}"
					:value="$rideRequestTimeLimit->time_limit_seconds ?? ''"
					required
				/>
				<button type='submit' class="btn btn-warning btn-sm me-1">{{ __('Save') }}</button>
			</form>
		</div>
	</div>
</div>
@endsection