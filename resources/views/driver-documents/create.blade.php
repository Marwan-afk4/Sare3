@extends('layouts.app')
@php
	$currentPage = 'driver-documents';
@endphp
@section('title', __('Create Driver Document'))
@section('content')
<div class="container">
	<h1>{{ __('Create Driver Document') }}</h1>
	<div class="mb-3">
		<a href="{{ route('driver-documents.index') }}" class="btn btn-secondary btn-sm me-1"> <i class="fa fa-arrow-right"></i> {{__('Back to')}} {{__('Driver Documents')}}</a>
	</div>
	<div class="main-card mb-3 card">
		<div class="card-body">
			<form method='POST' action='{{ route('driver-documents.store') }}' class="needs-validation" novalidate>
				@csrf
				<x-form-select 
					name="driver_id"
					type="select"
					label="{{__('Driver')}}"
					:selected="$driver_document->driver_id ?? ''"
					required
					:options="$drivers"
				/>
				<x-form-select 
					name="document_type_id"
					type="select"
					label="{{__('Document Type')}}"
					:selected="$driver_document->document_type_id ?? ''"
					required
					:options="$documentTypes"
				/>
				<x-form-input 
					name="image_path"
					type="text"
					label="{{__('Image Path')}}"
					required
				/>
				<button type='submit' class="btn btn-primary btn-sm me-1">{{ __('Add') }}</button>
			</form>
		</div>
	</div>
</div>@endsection