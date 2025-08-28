@extends('layouts.app')
@php
	$currentPage = 'zones';
@endphp
@section('title', __('Edit Zone'))
@section('content')
<div class="container">
	<h1>{{ __('Edit Zone') }}</h1>
	<div class="mb-3">
		<a href="{{ route('zones.index') }}" class="btn btn-secondary btn-sm me-1"> <i class="fa fa-arrow-right"></i> {{__('Back to')}} {{__('Zones')}}</a>
		{{-- <a href='{{ route('zones.show', $zone) }}' class="btn btn-primary btn-sm me-1">{{ __("Details") }} <i class="fa fa-eye"></i></a> --}}
	</div>
	<div class="main-card mb-3 card">
		<div class="card-body">
			<form method='POST' action='{{ route('zones.update', $zone->id) }}' class="needs-validation" novalidate>
				@csrf
				@method('PUT')
				<x-form-input
					name="name"
					type="text"
					label="{{__('Name')}}"
					:value="$zone->name ?? ''"
					required
				/>
				<x-form-input
					name="from_lat"
					type="text"
					label="{{__('From Lat')}}"
					:value="$zone->from_lat ?? ''"
					required
				/>
				<x-form-input
					name="from_lng"
					type="text"
					label="{{__('From Lng')}}"
					:value="$zone->from_lng ?? ''"
					required
				/>
				<x-form-input
					name="to_lat"
					type="text"
					label="{{__('To Lat')}}"
					:value="$zone->to_lat ?? ''"
					required
				/>
				<x-form-input
					name="to_lng"
					type="text"
					label="{{__('To Lng')}}"
					:value="$zone->to_lng ?? ''"
					required
				/>
				<button type='submit' class="btn btn-warning btn-sm me-1">{{ __('Save') }}</button>
			</form>
		</div>
	</div>
</div>
@endsection
