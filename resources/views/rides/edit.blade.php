@extends('layouts.app')
@php
	$currentPage = 'rides';
@endphp
@section('title', __('Edit Ride'))
@section('content')
<div class="container">
	<h1>{{ __('Edit Ride') }}</h1>
	<div class="mb-3">
		<a href="{{ route('rides.index') }}" class="btn btn-secondary btn-sm me-1"> <i class="fa fa-arrow-right"></i> {{__('Back to')}} {{__('Rides')}}</a>
		<a href='{{ route('rides.show', $ride) }}' class="btn btn-primary btn-sm me-1">{{ __("Details") }} <i class="fa fa-eye"></i></a>
	</div>
	<div class="main-card mb-3 card">
		<div class="card-body">
			<form method='POST' action='{{ route('rides.update', $ride->id) }}' class="needs-validation" novalidate>
				@csrf
				@method('PUT')
				<x-form-select 
					name="user_id"
					type="select"
					label="{{__('User')}}"
					:selected="$ride->user_id ?? ''"
					required
					:options="$users"
				/>
				<x-form-select 
					name="driver_id"
					type="select"
					label="{{__('Driver')}}"
					:selected="$ride->driver_id ?? ''"
					:options="$drivers"
				/>
				<x-form-select 
					name="car_category_id"
					type="select"
					label="{{__('Car Category')}}"
					:selected="$ride->car_category_id ?? ''"
					:options="$carCategories"
				/>
				<x-form-input 
					name="pickup_lat"
					type="text"
					label="{{__('Pickup Lat')}}"
					:value="$ride->pickup_lat ?? ''"
					required
				/>
				<x-form-input 
					name="pickup_lng"
					type="text"
					label="{{__('Pickup Lng')}}"
					:value="$ride->pickup_lng ?? ''"
					required
				/>
				<x-form-input 
					name="pickup_address"
					type="text"
					label="{{__('Pickup Address')}}"
					:value="$ride->pickup_address ?? ''"
				/>
				<x-form-input 
					name="dropoff_lat"
					type="text"
					label="{{__('Dropoff Lat')}}"
					:value="$ride->dropoff_lat ?? ''"
					required
				/>
				<x-form-input 
					name="dropoff_lng"
					type="text"
					label="{{__('Dropoff Lng')}}"
					:value="$ride->dropoff_lng ?? ''"
					required
				/>
				<x-form-input 
					name="dropoff_address"
					type="text"
					label="{{__('Dropoff Address')}}"
					:value="$ride->dropoff_address ?? ''"
				/>
				<x-form-input 
					name="estimated_km"
					type="text"
					label="{{__('Estimated Km')}}"
					:value="$ride->estimated_km ?? ''"
				/>
				<x-form-input 
					name="estimated_time"
					type="text"
					label="{{__('Estimated Time')}}"
					:value="$ride->estimated_time ?? ''"
				/>
				<x-form-input 
					name="calculated_initial_price"
					type="text"
					label="{{__('Calculated Initial Price')}}"
					:value="$ride->calculated_initial_price ?? ''"
				/>
				<x-form-input 
					name="route_points"
					type="text"
					label="{{__('Route Points')}}"
					:value="$ride->route_points ?? ''"
				/>
				<x-form-input 
					name="calculated_final_price"
					type="text"
					label="{{__('Calculated Final Price')}}"
					:value="$ride->calculated_final_price ?? ''"
				/>
				<x-form-input 
					name="started_at"
					type="text"
					label="{{__('Started At')}}"
					:value="$ride->started_at ?? ''"
				/>
				<x-form-input 
					name="ended_at"
					type="text"
					label="{{__('Ended At')}}"
					:value="$ride->ended_at ?? ''"
				/>
				<x-form-input 
					name="time_taken"
					type="text"
					label="{{__('Time Taken')}}"
					:value="$ride->time_taken ?? ''"
				/>
				<x-form-select 
					name="firebase_ride_id"
					type="select"
					label="{{__('Firebase Ride')}}"
					:selected="$ride->firebase_ride_id ?? ''"
					:options="$firebaseRides"
				/>
				<x-form-input 
					name="status"
					type="text"
					label="{{__('Status')}}"
					:value="$ride->status ?? ''"
					required
				/>
				<button type='submit' class="btn btn-warning btn-sm me-1">{{ __('Save') }}</button>
			</form>
		</div>
	</div>
</div>
@endsection