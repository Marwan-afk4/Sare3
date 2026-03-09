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
			<form method="POST" action="{{ route('driver-documents.store') }}" enctype="multipart/form-data" class="needs-validation" novalidate>
				@csrf
				<x-form-select
					name="driver_id"
					type="select"
					label="{{ __('Driver') }}"
					:selected="old('driver_id', '')"
					required
					:options="$drivers"
				/>
				<x-form-select
					name="document_type_id"
					type="select"
					label="{{ __('Document Type') }}"
					:selected="old('document_type_id', '')"
					required
					:options="$documentTypes"
				/>
				<div class="mb-3">
					<label for="document_file" class="form-label">{{ __('Document File') }} <span class="text-danger">*</span></label>
					<input type="file" class="form-control @error('document_file') is-invalid @enderror"
						   id="document_file" name="document_file" accept="image/*" required>
					@error('document_file')
						<div class="invalid-feedback">{{ $message }}</div>
					@enderror
				</div>
				<button type="submit" class="btn btn-primary btn-sm me-1">{{ __('Add') }}</button>
			</form>
		</div>
	</div>
</div>@endsection