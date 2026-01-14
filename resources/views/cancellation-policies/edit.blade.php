@extends('layouts.app')
@php
	$currentPage = 'cancellation-policies';
@endphp
@section('title', __('Edit Cancellation Policy'))
@section('content')
<div class="container">
	<h1>{{ __('Edit Cancellation Policy') }}</h1>
	<div class="mb-3">
		<a href="{{ route('cancellation-policies.index') }}" class="btn btn-secondary btn-sm me-1"> <i class="fa fa-arrow-right"></i> {{__('Back to')}} {{__('Cancellation Policies')}}</a>
		<a href='{{ route('cancellation-policies.show', $cancellationPolicy) }}' class="btn btn-primary btn-sm me-1">{{ __("Details") }} <i class="fa fa-eye"></i></a>
	</div>
	<div class="main-card mb-3 card">
		<div class="card-body">
			<form method='POST' action='{{ route('cancellation-policies.update', $cancellationPolicy->id) }}' class="needs-validation" novalidate>
				@csrf
				@method('PUT')
				<x-form-input
					name="name"
					type="text"
					label="{{__('Name')}}"
					:value="$cancellationPolicy->name ?? ''"
					required
				/>
				<x-form-select
					name="user_type"
					type="select"
					:selected="$cancellationPolicy->user_type ?? ''"
					:options="$userTypes"
					label="{{__('User Type')}}"
					required
				/>
				<x-form-select
					name="zone_id"
					type="select"
					:selected="$cancellationPolicy->zone_id ?? ''"
					:options="$zones"
					label="{{__('Zone')}}"
					required
				/>
				<x-form-input
					name="time_limit_minutes"
					type="number"
					label="{{__('Time Limit (Minutes)')}}"
					:value="$cancellationPolicy->time_limit_minutes ?? ''"
					required
					min="0"
				/>
				<x-form-input
					name="penalty_amount"
					type="number"
					label="{{__('Penalty Amount')}}"
					:value="$cancellationPolicy->penalty_amount ?? ''"
				/>
				<x-form-input
					name="penalty_percent"
					type="number"
					label="{{__('Penalty Percent')}}"
					:value="$cancellationPolicy->penalty_percent ?? ''"
				/>
				<x-form-textarea
					name="description"
					type="text"
					label="{{__('Description')}}"
					:value="$cancellationPolicy->description ?? ''"
				/>
                <x-form-select
					name="status"
					type="select"
					label="{{__('Status')}}"
                    :selected="$cancellationPolicy->status->value ?? ''"
                    :options="$statuses"
					required
				/>
				<button type='submit' class="btn btn-warning btn-sm me-1">{{ __('Save') }}</button>
			</form>
		</div>
	</div>
</div>
@endsection
