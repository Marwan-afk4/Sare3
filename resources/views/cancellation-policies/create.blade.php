@extends('layouts.app')
@php
	$currentPage = 'cancellation-policies';
@endphp
@section('title', __('Create Cancellation Policy'))
@section('content')
<div class="container">
	<h1>{{ __('Create Cancellation Policy') }}</h1>
	<div class="mb-3">
		<a href="{{ route('cancellation-policies.index') }}" class="btn btn-secondary btn-sm me-1"> <i class="fa fa-arrow-right"></i> {{__('Back to')}} {{__('Cancellation Policies')}}</a>
	</div>
	<div class="main-card mb-3 card">
		<div class="card-body">
			<form method='POST' action='{{ route('cancellation-policies.store') }}' class="needs-validation" novalidate>
				@csrf
				<x-form-input
					name="name"
					type="text"
					label="{{__('Name')}}"
					required
				/>
				<x-form-select
					name="user_type"
					type="select"
					:options="$userTypes"
					label="{{__('User Type')}}"
					required
				/>
				<x-form-select
					name="zone_id"
					type="select"
					:options="$zones"
					label="{{__('Zone')}}"
					required
				/>
				<x-form-input
					name="time_limit_minutes"
					type="number"
					label="{{__('Time Limit (Minutes)')}}"
					required
					min="0"
				/>
				<x-form-input
					name="penalty_amount"
					type="number"
					label="{{__('Penalty Amount')}}"
				/>
				<x-form-input
					name="penalty_percent"
					type="text"
					label="{{__('Penalty Percent')}}"
				/>
				<x-form-textarea
					name="description"
					type="text"
					label="{{__('Description')}}"
				/>
				<x-form-select
					name="status"
					type="select"
                    :options="$statuses"
					label="{{__('Status')}}"
					required
				/>
				<button type='submit' class="btn btn-primary btn-sm me-1">{{ __('Add') }}</button>
			</form>
		</div>
	</div>
</div>@endsection
