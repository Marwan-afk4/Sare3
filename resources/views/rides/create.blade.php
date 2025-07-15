@extends('layouts.app')
@php
	$currentPage = 'rides';
@endphp
@section('title', __('Create Ride'))
@section('content')
<div class="container">
	<h1>{{ __('Create Ride') }}</h1>
	<div class="mb-3">
		<a href="{{ route('rides.index') }}" class="btn btn-secondary btn-sm me-1"> <i class="fa fa-arrow-right"></i> {{__('Back to')}} {{__('Rides')}}</a>
	</div>
	<div class="main-card mb-3 card">
		<div class="card-body">
			<form method='POST' action='{{ route('rides.store') }}' class="needs-validation" novalidate>
				@csrf
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
					required
				/>
				<x-form-input 
					name="pickup_lng"
					type="text"
					label="{{__('Pickup Lng')}}"
					required
				/>
				<x-form-input 
					name="pickup_address"
					type="text"
					label="{{__('Pickup Address')}}"
				/>
				<x-form-input 
					name="dropoff_lat"
					type="text"
					label="{{__('Dropoff Lat')}}"
					required
				/>
				<x-form-input 
					name="dropoff_lng"
					type="text"
					label="{{__('Dropoff Lng')}}"
					required
				/>
				<x-form-input 
					name="dropoff_address"
					type="text"
					label="{{__('Dropoff Address')}}"
				/>
				<x-form-input 
					name="estimated_km"
					type="text"
					label="{{__('Estimated Km')}}"
				/>
				<x-form-input 
					name="estimated_time"
					type="text"
					label="{{__('Estimated Time')}}"
				/>
				<x-form-input 
					name="calculated_initial_price"
					type="text"
					label="{{__('Calculated Initial Price')}}"
				/>
				<x-form-input 
					name="route_points"
					type="text"
					label="{{__('Route Points')}}"
				/>
				<x-form-input 
					name="calculated_final_price"
					type="text"
					label="{{__('Calculated Final Price')}}"
				/>
				<x-form-input 
					name="started_at"
					type="text"
					label="{{__('Started At')}}"
				/>
				<x-form-input 
					name="ended_at"
					type="text"
					label="{{__('Ended At')}}"
				/>
				<x-form-input 
					name="time_taken"
					type="text"
					label="{{__('Time Taken')}}"
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
					required
				/>
				<button type='submit' class="btn btn-primary btn-sm me-1">{{ __('Add') }}</button>
			</form>
		</div>
	</div>
</div>@endsection