@extends('layouts.app')
@php
	$currentPage = 'cancelation-rides';
@endphp
@section('title', __('Create Cancelation Ride'))
@section('content')
<div class="container">
	<h1>{{ __('Create Cancelation Ride') }}</h1>
	<div class="mb-3">
		<a href="{{ route('cancelation-rides.index') }}" class="btn btn-secondary btn-sm me-1"> <i class="fa fa-arrow-right"></i> {{__('Back to')}} {{__('Cancelation Rides')}}</a>
	</div>
	<div class="main-card mb-3 card">
		<div class="card-body">
			<form method='POST' action='{{ route('cancelation-rides.store') }}' class="needs-validation" novalidate>
				@csrf
				<x-form-select 
					name="ride_id"
					type="select"
					label="{{__('Ride')}}"
					:selected="$cancelation_ride->ride_id ?? ''"
					required
					:options="$rides"
				/>
				<x-form-select 
					name="user_id"
					type="select"
					label="{{__('User')}}"
					:selected="$cancelation_ride->user_id ?? ''"
					:options="$users"
				/>
				<x-form-select 
					name="driver_id"
					type="select"
					label="{{__('Driver')}}"
					:selected="$cancelation_ride->driver_id ?? ''"
					:options="$drivers"
				/>
				<x-form-select 
					name="cancelation_policy_id"
					type="select"
					label="{{__('Cancelation Policy')}}"
					:selected="$cancelation_ride->cancelation_policy_id ?? ''"
					:options="$cancelationPolicies"
				/>
				<x-form-input 
					name="canceled_by"
					type="text"
					label="{{__('Canceled By')}}"
				/>
				<x-form-input 
					name="penalty_applied"
					type="text"
					label="{{__('Penalty Applied')}}"
					required
				/>
				<x-form-input 
					name="penalty_amount"
					type="text"
					label="{{__('Penalty Amount')}}"
				/>
				<x-form-input 
					name="canceled_at"
					type="text"
					label="{{__('Canceled At')}}"
				/>
				<x-form-input 
					name="reason"
					type="text"
					label="{{__('Reason')}}"
				/>
				<button type='submit' class="btn btn-primary btn-sm me-1">{{ __('Add') }}</button>
			</form>
		</div>
	</div>
</div>@endsection