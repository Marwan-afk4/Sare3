@extends('layouts.app')
@php
	$currentPage = 'car-categories';
@endphp
@section('title', __('Create Car Category'))
@section('content')
<div class="container">
	<h1>{{ __('Create Car Category') }}</h1>
	<div class="mb-3">
		<a href="{{ route('car-categories.index') }}" class="btn btn-secondary btn-sm me-1"> <i class="fa fa-arrow-right"></i> {{__('Back to')}} {{__('Car Categories')}}</a>
	</div>
	<div class="main-card mb-3 card">
		<div class="card-body">
			<form method='POST' action='{{ route('car-categories.store') }}'  enctype="multipart/form-data" novalidate>
				@csrf
				<x-form-input
					name="name"
					type="text"
					label="{{__('Name')}}"
					required
				/>
				<x-form-input
					name="description"
					type="text"
					label="{{__('Description')}}"
                    required
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
                    :attributes="['step' => '0.01']"
					required
				/>
				<x-form-input
					name="price_per_km"
					type="number"
					label="{{__('Price Per Km')}}"
                    required
                    :attributes="['step' => '0.01']"
				/>
				<x-form-input
					name="price_per_time"
					type="number"
					label="{{__('Price Per Time')}}"
					required
                    :attributes="['step' => '0.01']"
				/>
				<button type='submit' class="btn btn-primary btn-sm me-1">{{ __('Add') }}</button>
			</form>
		</div>
	</div>
</div>@endsection
