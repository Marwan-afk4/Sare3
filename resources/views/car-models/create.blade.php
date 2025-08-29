@extends('layouts.app')
@php
	$currentPage = 'car-models';
@endphp
@section('title', __('Create Car Model'))
@section('content')
<div class="container">
	<h1>{{ __('Create Car Model') }}</h1>
	<div class="mb-3">
		<a href="{{ route('car-models.index') }}" class="btn btn-secondary btn-sm me-1"> <i class="fa fa-arrow-right"></i> {{__('Back to')}} {{__('Car Models')}}</a>
	</div>
	<div class="main-card mb-3 card">
		<div class="card-body">
			<form method='POST' action='{{ route('car-models.store') }}' class="needs-validation" novalidate>
				@csrf
				<x-form-select
                    name="car_categories"
                    label="{{ __('Car Categories') }}"
                    :options="$carCategories"
                    :selected="old('car_categories', [])"
                    :required="true"
                    :multiple="true"
                />



				<x-form-input
					name="name"
					type="text"
					label="{{__('Name')}}"
					required
				/>
				<button type='submit' class="btn btn-primary btn-sm me-1">{{ __('Add') }}</button>
			</form>
		</div>
	</div>
</div>@endsection
