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
			<form method="POST" action="{{ route('driver-documents.update', $driverDocument->id) }}" enctype="multipart/form-data" class="needs-validation" novalidate>
				@csrf
				@method('PUT')
				<x-form-select
					name="driver_id"
					type="select"
					label="{{ __('Driver') }}"
					:selected="old('driver_id', $driverDocument->driver_id ?? '')"
					required
					:options="$drivers"
				/>
				<x-form-select
					name="document_type_id"
					type="select"
					label="{{ __('Document Type') }}"
					:selected="old('document_type_id', $driverDocument->document_type_id ?? '')"
					required
					:options="$documentTypes"
				/>
				<div class="mb-3">
					<label for="document_file" class="form-label">{{ __('Document File') }}</label>
					<input type="file" class="form-control @error('document_file') is-invalid @enderror"
						   id="document_file" name="document_file" accept="image/*">
					@error('document_file')
						<div class="invalid-feedback">{{ $message }}</div>
					@enderror
					<small class="text-muted">{{ __('Leave empty to keep the current file.') }}</small>
					@if($driverDocument->image_link)
						<div class="mt-2">
							<small class="text-muted">{{ __('Current document:') }}</small><br>
							<img src="{{ $driverDocument->image_link }}" alt="{{ $driverDocument->documentType?->name ?? __('Document') }}"
								 class="img-thumbnail" style="max-width: 200px;">
						</div>
					@endif
				</div>
				<button type="submit" class="btn btn-warning btn-sm me-1">{{ __('Save') }}</button>
			</form>
		</div>
	</div>
</div>
@endsection