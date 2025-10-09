@extends('layouts.app')
@php
	$currentPage = 'car-types';
@endphp
@section('title', __('Edit Car Type'))
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
				<x-form-select
					name="car_category_id"
					type="select"
					label="{{__('Car Category')}}"
					:selected="$carType->car_category_id ?? ''"
					required
					:options="$carCategories"
				/>
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
