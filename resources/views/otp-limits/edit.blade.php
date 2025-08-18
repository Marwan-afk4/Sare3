@extends('layouts.app')
@php
	$currentPage = 'otp-limits';
@endphp
@section('title', __('Edit Otp Limit'))
@section('content')
<div class="container">
	<h1>{{ __('Edit Otp Limit') }}</h1>
	<div class="mb-3">
		<a href="{{ route('otp-limits.index') }}" class="btn btn-secondary btn-sm me-1"> <i class="fa fa-arrow-right"></i> {{__('Back to')}} {{__('Otp Limits')}}</a>
		<a href='{{ route('otp-limits.show', $otpLimit) }}' class="btn btn-primary btn-sm me-1">{{ __("Details") }} <i class="fa fa-eye"></i></a>
	</div>
	<div class="main-card mb-3 card">
		<div class="card-body">
			<form method='POST' action='{{ route('otp-limits.update', $otpLimit->id) }}' class="needs-validation" novalidate>
				@csrf
				@method('PUT')
				<x-form-select
					name="type"
					type="select"
					label="{{__('Type')}}"
                    :options="$otpTypes"
                    :selected="$otpLimit->type->value ?? ''"
                    required
				/>
				<x-form-input
					name="otp_limit"
					type="number"
					label="{{__('Otp Limit')}}"
					:value="$otpLimit->otp_limit ?? ''"
				/>
				<button type='submit' class="btn btn-warning btn-sm me-1">{{ __('Save') }}</button>
			</form>
		</div>
	</div>
</div>
@endsection
