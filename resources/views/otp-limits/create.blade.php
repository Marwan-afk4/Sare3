@extends('layouts.app')
@php
	$currentPage = 'otp-limits';
@endphp
@section('title', __('Create Otp Limit'))
@section('content')
<div class="container">
	<h1>{{ __('Create Otp Limit') }}</h1>
	<div class="mb-3">
		<a href="{{ route('otp-limits.index') }}" class="btn btn-secondary btn-sm me-1"> <i class="fa fa-arrow-right"></i> {{__('Back to')}} {{__('Otp Limits')}}</a>
	</div>
	<div class="main-card mb-3 card">
		<div class="card-body">
			<form method='POST' action='{{ route('otp-limits.store') }}' class="needs-validation" novalidate>
				@csrf
				<x-form-select
					name="type"
					type="select"
					label="{{__('Type')}}"
					:options="$otpTypes"
                    required
				/>
				<x-form-input
					name="otp_limit"
					type="number"
					label="{{__('Otp Limit')}}"
				/>
				<button type='submit' class="btn btn-primary btn-sm me-1">{{ __('Add') }}</button>
			</form>
		</div>
	</div>
</div>@endsection
