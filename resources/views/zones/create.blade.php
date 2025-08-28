@extends('layouts.app')
@php
	$currentPage = 'zones';
@endphp
@section('title', __('Create Zone'))
@section('content')
<div class="container">
	<h1>{{ __('Create Zone') }}</h1>
	<div class="mb-3">
		<a href="{{ route('zones.index') }}" class="btn btn-secondary btn-sm me-1"> <i class="fa fa-arrow-right"></i> {{__('Back to')}} {{__('Zones')}}</a>
	</div>
	<div class="main-card mb-3 card">
		<div class="card-body">
			<form method='POST' action='{{ route('zones.store') }}' class="needs-validation" novalidate>
				@csrf
				<x-form-input 
					name="name"
					type="text"
					label="{{__('Name')}}"
					required
				/>
				<x-form-input 
					name="from_lat"
					type="text"
					label="{{__('From Lat')}}"
					required
				/>
				<x-form-input 
					name="from_lng"
					type="text"
					label="{{__('From Lng')}}"
					required
				/>
				<x-form-input 
					name="to_lat"
					type="text"
					label="{{__('To Lat')}}"
					required
				/>
				<x-form-input 
					name="to_lng"
					type="text"
					label="{{__('To Lng')}}"
					required
				/>
				<button type='submit' class="btn btn-primary btn-sm me-1">{{ __('Add') }}</button>
			</form>
		</div>
	</div>
</div>@endsection