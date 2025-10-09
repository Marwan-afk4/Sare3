@extends('layouts.app')
@php
	$currentPage = 'car-models';
@endphp
@section('title', __('Edit Car Model'))
@section('content')
<div class="container">
	<h1>{{ __('Edit Car Model') }}</h1>
	<div class="mb-3">
		<a href="{{ route('car-models.index') }}" class="btn btn-secondary btn-sm me-1"> <i class="fa fa-arrow-right"></i> {{__('Back to')}} {{__('Car Models')}}</a>
		<a href='{{ route('car-models.show', $carModel) }}' class="btn btn-primary btn-sm me-1">{{ __("Details") }} <i class="fa fa-eye"></i></a>
	</div>
	<div class="main-card mb-3 card">
		<div class="card-body">
			<form method='POST' action='{{ route('car-models.update', $carModel->id) }}' class="needs-validation" novalidate>
				@csrf
				@method('PUT')
				<x-form-input 
					name="name"
					type="text"
					label="{{__('Name')}}"
					:value="$carModel->name ?? ''"
					required
				/>
				<button type='submit' class="btn btn-warning btn-sm me-1">{{ __('Save') }}</button>
			</form>
		</div>
	</div>
</div>
@endsection