@extends('layouts.app')
@php
	$currentPage = 'driver-documents';
@endphp
@section('title', __('Edit Driver Document'))
@section('content')
<div class="container">
	<h1>{{ __('Edit Driver Document') }}</h1>
	<div class="mb-3">
		<a href="{{ route('driver-documents.index') }}" class="btn btn-secondary btn-sm me-1"> <i class="fa fa-arrow-right"></i> {{__('Back to')}} {{__('Driver Documents')}}</a>
		<a href='{{ route('driver-documents.show', $driverDocument) }}' class="btn btn-primary btn-sm me-1">{{ __("Details") }} <i class="fa fa-eye"></i></a>
	</div>
	<div class="main-card mb-3 card">
		<div class="card-body">
			<form method='POST' action='{{ route('driver-documents.update', $driverDocument->id) }}' class="needs-validation" novalidate>
				@csrf
				@method('PUT')
				<x-form-select 
					name="driver_id"
					type="select"
					label="{{__('Driver')}}"
					:selected="$driverDocument->driver_id ?? ''"
					required
					:options="$drivers"
				/>
				<x-form-select 
					name="document_type_id"
					type="select"
					label="{{__('Document Type')}}"
					:selected="$driverDocument->document_type_id ?? ''"
					required
					:options="$documentTypes"
				/>
				<x-form-input 
					name="image_path"
					type="text"
					label="{{__('Image Path')}}"
					:value="$driverDocument->image_path ?? ''"
					required
				/>
				<button type='submit' class="btn btn-warning btn-sm me-1">{{ __('Save') }}</button>
			</form>
		</div>
	</div>
</div>
@endsection