@extends('layouts.app')
@php
	$currentPage = 'cancelation-rides';
@endphp
@section('title', __('Edit Cancelation Ride'))
@section('content')
<div class="container">
	<h1>{{ __('Edit Cancelation Ride') }}</h1>
	<div class="mb-3">
		<a href="{{ route('cancelation-rides.index') }}" class="btn btn-secondary btn-sm me-1"> <i class="fa fa-arrow-right"></i> {{__('Back to')}} {{__('Cancelation Rides')}}</a>
		<a href='{{ route('cancelation-rides.show', $cancelationRide) }}' class="btn btn-primary btn-sm me-1">{{ __("Details") }} <i class="fa fa-eye"></i></a>
	</div>
	<div class="main-card mb-3 card">
		<div class="card-body">
			<form method='POST' action='{{ route('cancelation-rides.update', $cancelationRide->id) }}' class="needs-validation" novalidate>
				@csrf
				@method('PUT')
				<x-form-select 
					name="ride_id"
					type="select"
					label="{{__('Ride')}}"
					:selected="$cancelationRide->ride_id ?? ''"
					required
					:options="$rides"
				/>
				<x-form-select 
					name="user_id"
					type="select"
					label="{{__('User')}}"
					:selected="$cancelationRide->user_id ?? ''"
					:options="$users"
				/>
				<x-form-select 
					name="driver_id"
					type="select"
					label="{{__('Driver')}}"
					:selected="$cancelationRide->driver_id ?? ''"
					:options="$drivers"
				/>
				<x-form-select 
					name="cancelation_policy_id"
					type="select"
					label="{{__('Cancelation Policy')}}"
					:selected="$cancelationRide->cancelation_policy_id ?? ''"
					:options="$cancelationPolicies"
				/>
				<x-form-input 
					name="canceled_by"
					type="text"
					label="{{__('Canceled By')}}"
					:value="$cancelationRide->canceled_by ?? ''"
				/>
				<x-form-input 
					name="penalty_applied"
					type="text"
					label="{{__('Penalty Applied')}}"
					:value="$cancelationRide->penalty_applied ?? ''"
					required
				/>
				<x-form-input 
					name="penalty_amount"
					type="text"
					label="{{__('Penalty Amount')}}"
					:value="$cancelationRide->penalty_amount ?? ''"
				/>
				<x-form-input 
					name="canceled_at"
					type="text"
					label="{{__('Canceled At')}}"
					:value="$cancelationRide->canceled_at ?? ''"
				/>
				<x-form-input 
					name="reason"
					type="text"
					label="{{__('Reason')}}"
					:value="$cancelationRide->reason ?? ''"
				/>
				<button type='submit' class="btn btn-warning btn-sm me-1">{{ __('Save') }}</button>
			</form>
		</div>
	</div>
</div>
@endsection