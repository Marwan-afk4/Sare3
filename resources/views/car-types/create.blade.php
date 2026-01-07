@extends('layouts.app')
@php
	$currentPage = 'car-types';
@endphp
@section('title', __('Create Car Type'))

@push('styles')
<link rel="stylesheet" href="{{ asset('css/car-type-categories.css') }}">
@endpush

@section('content')
<div class="container">
	<h1>{{ __('Create Car Type') }}</h1>
	<div class="mb-3">
		<a href="{{ route('car-types.index') }}" class="btn btn-secondary btn-sm me-1"> <i class="fa fa-arrow-right"></i> {{__('Back to')}} {{__('Car Types')}}</a>
	</div>
	<div class="main-card mb-3 card">
		<div class="card-body">
			<form method='POST' action='{{ route('car-types.store') }}' class="needs-validation" novalidate>
				@csrf

				<x-form-select
					name="car_model_id"
					type="select"
					label="{{__('Car Model/Brand')}}"
					:selected="old('car_model_id', '')"
					required
					:options="$carModels"
				/>

				<div class="form-floating mb-3 required">
					<select name="car_category_ids[]" id="car_category_ids_select"
						class="form-select @error('car_category_ids') is-invalid @enderror"
						multiple required>
						@foreach($carCategories as $key => $value)
							<option value="{{ $key }}">{{ $value }}</option>
						@endforeach
					</select>
					<label for="car_category_ids_select">
						<i class="fas fa-tags me-1"></i>{{ __('Car Categories') }} <span class="text-danger">*</span>
					</label>
					@error('car_category_ids')
						<span class="text-danger small">{{ $message }}</span>
					@enderror
					<small class="form-text text-muted">{{ __('Select one or more categories for this car type') }}</small>
				</div>

				<x-form-input
					name="type_name"
					type="text"
					label="{{__('Type Name')}}"
					required
				/>

				<div class="row">
					<div class="col-md-6">
						<x-form-input
							name="year_from"
							type="number"
							label="{{__('Year From')}}"
							min="1900"
							max="{{ date('Y') + 10 }}"
						/>
					</div>
					<div class="col-md-6">
						<x-form-input
							name="year_to"
							type="number"
							label="{{__('Year To')}}"
							min="1900"
							max="{{ date('Y') + 10 }}"
						/>
					</div>
				</div>
				<small class="form-text text-muted mb-3">{{ __('Leave empty for no year restriction. Year To must be greater than or equal to Year From.') }}</small>

				<x-form-input
					name="description"
					type="text"
					label="{{__('Description')}}"
				/>
				<button type='submit' class="btn btn-primary btn-sm me-1">{{ __('Add') }}</button>
			</form>
		</div>
	</div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/car-type-categories.js') }}"></script>
<script src="{{ asset('js/car-type-year-validation.js') }}"></script>
@endpush
