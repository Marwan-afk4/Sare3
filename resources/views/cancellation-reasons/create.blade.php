@extends('layouts.app')
@php
	$currentPage = 'cancellation-reasons';
@endphp
@section('title', __('Create Cancellation Reason'))
@section('content')
<div class="container">
	<h1>{{ __('Create Cancellation Reason') }}</h1>
	<div class="mb-3">
		<a href="{{ route('cancellation-reasons.index') }}" class="btn btn-secondary btn-sm me-1">
			<i class="fa fa-arrow-right"></i> {{__('Back to')}} {{__('Cancellation Reasons')}}
		</a>
	</div>
	<div class="main-card mb-3 card">
		<div class="card-body">
			<form method='POST' action='{{ route('cancellation-reasons.store') }}' class="needs-validation" novalidate>
				@csrf
				<x-form-input
					name="reason"
					type="text"
					label="{{__('Reason')}}"
					required
				/>
				<x-form-select
					name="type"
					type="select"
					label="{{__('Type')}}"
                    :options="$types"
					required
				/>

				{{-- Switch for is_active --}}
				<div class="mb-3">
					<label for="is_active" class="form-label">{{ __('Is Active') }}</label>
					<div class="form-check form-switch">
						<input type="hidden" name="is_active" value="0"> 
						<input
							class="form-check-input"
							type="checkbox"
							id="is_active"
							name="is_active"
							value="1"
							{{ old('is_active') ? 'checked' : '' }}>
						<label class="form-check-label" for="is_active">
							{{ old('is_active') ? __('Active') : __('Inactive') }}
						</label>
					</div>
				</div>

				<button type='submit' class="btn btn-primary btn-sm me-1">{{ __('Add') }}</button>
			</form>
		</div>
	</div>
</div>
@endsection
