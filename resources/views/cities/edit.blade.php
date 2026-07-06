@extends('layouts.app')
@php
	$currentPage = 'cities';
@endphp
@section('title', __('Edit City'))
@section('content')
<div class="container">
	<h1>{{ __('Edit City') }}</h1>
	<div class="mb-3">
		<a href="{{ route('cities.index') }}" class="btn btn-secondary btn-sm me-1"> <i class="fa fa-arrow-right"></i> {{__('Back to')}} {{__('Cities')}}</a>
		<a href='{{ route('cities.show', $city) }}' class="btn btn-primary btn-sm me-1">{{ __("Details") }} <i class="fa fa-eye"></i></a>
	</div>
	<div class="main-card mb-3 card">
		<div class="card-body">
			<form method='POST' action='{{ route('cities.update', $city->id) }}' class="needs-validation" novalidate>
				@csrf
				@method('PUT')
				<x-form-input 
					name="name"
					type="text"
					label="{{__('Name')}}"
					:value="$city->name ?? ''"
				/>
				<x-form-select 
					name="status"
					type="select"
					label="{{__('Status')}}"
					:selected="$city->status"
					required
					:options="$statuses"
				/>
				<button type='submit' class="btn btn-warning btn-sm me-1">{{ __('Save') }}</button>
			</form>
		</div>
	</div>
</div>
@endsection