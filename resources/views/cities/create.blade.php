@extends('layouts.app')
@php
	$currentPage = 'cities';
@endphp
@section('title', __('Create City'))
@section('content')
<div class="container">
	<h1>{{ __('Create City') }}</h1>
	<div class="mb-3">
		<a href="{{ route('cities.index') }}" class="btn btn-secondary btn-sm me-1"> <i class="fa fa-arrow-right"></i> {{__('Back to')}} {{__('Cities')}}</a>
	</div>
	<div class="main-card mb-3 card">
		<div class="card-body">
			<form method='POST' action='{{ route('cities.store') }}' class="needs-validation" novalidate>
				@csrf
				<x-form-input 
					name="name"
					type="text"
					label="{{__('Name')}}"
					required
				/>
				<x-form-select 
					name="status"
					type="select"
					label="{{__('Status')}}"
					required
					:options="$statuses"
				/>
				<button type='submit' class="btn btn-primary btn-sm me-1">{{ __('Add') }}</button>
			</form>
		</div>
	</div>
</div>@endsection