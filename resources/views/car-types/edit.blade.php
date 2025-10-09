@extends('layouts.app')
@php
	$currentPage = 'car-types';
@endphp
@section('title', __('Edit Car Type'))

@push('styles')
<link rel="stylesheet" href="{{ asset('css/car-type-categories.css') }}">
@endpush

@section('content')
<div class="container">
	<h1>{{ __('Edit Car Type') }}</h1>
	<div class="mb-3">
		<a href="{{ route('car-types.index') }}" class="btn btn-secondary btn-sm me-1"> <i class="fa fa-arrow-right"></i> {{__('Back to')}} {{__('Car Types')}}</a>
		{{-- <a href='{{ route('car-types.show', $carType) }}' class="btn btn-primary btn-sm me-1">{{ __("Details") }} <i class="fa fa-eye"></i></a> --}}
	</div>
	<div class="main-card mb-3 card">
		<div class="card-body">
			<form method='POST' action='{{ route('car-types.update', $carType->id) }}' class="needs-validation" novalidate>
				@csrf
				@method('PUT')
				
				<div class="form-floating mb-3 required">
					<select name="car_category_ids[]" id="car_category_ids_select" 
						class="form-select @error('car_category_ids') is-invalid @enderror" 
						multiple required>
						@foreach($carCategories as $key => $value)
							<option value="{{ $key }}" 
								{{ in_array($key, $carType->carCategories->pluck('id')->toArray()) ? 'selected' : '' }}>
								{{ $value }}
							</option>
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
					:value="$carType->type_name ?? ''"
					required
				/>
				<x-form-input
					name="description"
					type="text"
					label="{{__('Description')}}"
					:value="$carType->description ?? ''"
				/>
				<button type='submit' class="btn btn-warning btn-sm me-1">{{ __('Save') }}</button>
			</form>
		</div>
	</div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/car-type-categories.js') }}"></script>
@endpush
