@extends('layouts.app')
@php
	$currentPage = 'cancellation-reasons';
@endphp
@section('title', __('Edit Cancellation Reason'))
@section('content')
<div class="container">
	<h1>{{ __('Edit Cancellation Reason') }}</h1>
	<div class="mb-3">
		<a href="{{ route('cancellation-reasons.index') }}" class="btn btn-secondary btn-sm me-1">
			<i class="fa fa-arrow-right"></i> {{__('Back to')}} {{__('Cancellation Reasons')}}
		</a>
		{{-- <a href='{{ route('cancellation-reasons.show', $cancellationReason) }}' class="btn btn-primary btn-sm me-1">
			{{ __("Details") }} <i class="fa fa-eye"></i>
		</a> --}}
	</div>
	<div class="main-card mb-3 card">
		<div class="card-body">
			<form method='POST' action='{{ route('cancellation-reasons.update', $cancellationReason->id) }}' class="needs-validation" novalidate>
				@csrf
				@method('PUT')

				<x-form-input
					name="reason"
					type="text"
					label="{{__('Reason')}}"
					:value="$cancellationReason->reason ?? ''"
					required
				/>

				<x-form-select
					name="type"
					type="select"
					label="{{__('Type')}}"
					:selected="$cancellationReason->type->value ?? ''"
                    :options="$types"
					required
				/>

				{{-- Switch for is_active --}}
				<div class="mb-3">
					<label for="is_active" class="form-label">{{ __('Is Active') }}</label>
					<div class="form-check form-switch">
						<input type="hidden" name="is_active" value="0"> <!-- default لو مطفي -->
						<input
							class="form-check-input"
							type="checkbox"
							id="is_active"
							name="is_active"
							value="1"
							{{ old('is_active', $cancellationReason->is_active) ? 'checked' : '' }}>
						<label class="form-check-label" for="is_active">
							{{ old('is_active', $cancellationReason->is_active) ? __('Active') : __('Inactive') }}
						</label>
					</div>
				</div>

				<button type='submit' class="btn btn-warning btn-sm me-1">{{ __('Save') }}</button>
			</form>
		</div>
	</div>
</div>
@endsection
