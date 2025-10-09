@extends('layouts.app')
@php
	$currentPage = 'car-types';
@endphp
@section('title', __('Create Car Type'))
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
					name="car_category_ids"
					type="select"
					label="{{__('Car Categories')}}"
					:selected="[]"
					required
					multiple
					:options="$carCategories"
				/>
				<x-form-input
					name="type_name"
					type="text"
					label="{{__('Type Name')}}"
					required
				/>
				<x-form-input
					name="description"
					type="text"
					label="{{__('Description')}}"
				/>
				<button type='submit' class="btn btn-primary btn-sm me-1">{{ __('Add') }}</button>
			</form>
		</div>
	</div>
</div>@endsection
