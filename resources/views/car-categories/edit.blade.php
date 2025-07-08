@extends('layouts.app')
@php
	$currentPage = 'car-categories';
@endphp
@section('title', __('Edit Car Category'))
@section('content')
<div class="container">
	<h1>{{ __('Edit Car Category') }}</h1>
	<div class="mb-3">
		<a href="{{ route('car-categories.index') }}" class="btn btn-secondary btn-sm me-1"> <i class="fa fa-arrow-right"></i> {{__('Back to')}} {{__('Car Categories')}}</a>
		<a href='{{ route('car-categories.show', $carCategory) }}' class="btn btn-primary btn-sm me-1">{{ __("Details") }} <i class="fa fa-eye"></i></a>
	</div>
	<div class="main-card mb-3 card">
		<div class="card-body">
			<form method='POST' action='{{ route('car-categories.update', $carCategory->id) }}'  enctype="multipart/form-data" novalidate>
				@csrf
				@method('PUT')
				<x-form-input
					name="name"
					type="text"
					label="{{__('Name')}}"
					:value="$carCategory->name ?? ''"
				/>
				<x-form-input
					name="description"
					type="text"
					label="{{__('Description')}}"
					:value="$carCategory->description ?? ''"
				/>
				<x-form-input
					name="icon"
					type="file"
					label="{{__('Icon')}}"
                    :attributes="['accept' => 'image/*']"
				/>
				<x-form-input
					name="base_price"
					type="number"
					label="{{__('Base Price')}}"
                    :value="$carCategory->base_price ?? ''"
                    :attributes="['step' => '0.01']"
				/>
				<x-form-input
					name="price_per_km"
					type="number"
					label="{{__('Price Per Km')}}"
                    :value="$carCategory->price_per_km ?? ''"
                    :attributes="['step' => '0.01']"
				/>
				<x-form-input
					name="price_per_time"
					type="number"
					label="{{__('Price Per Time')}}"
                    :value="$carCategory->price_per_time ?? ''"
                    :attributes="['step' => '0.01']"
				/>
				<button type='submit' class="btn btn-warning btn-sm me-1">{{ __('Save') }}</button>
			</form>
		</div>
	</div>
</div>
@endsection
