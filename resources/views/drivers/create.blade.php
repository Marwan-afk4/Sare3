@extends('layouts.app')
@php
	$currentPage = 'drivers';
@endphp
@section('title', __('Create Driver'))
@section('content')
<div class="container">
	<h1>{{ __('Create Driver') }}</h1>
	<div class="mb-3">
		<a href="{{ route('drivers.index') }}" class="btn btn-secondary btn-sm me-1"> <i class="fa fa-arrow-right"></i> {{ __('Back to') }} {{ __('Drivers') }}</a>
	</div>
	<div class="main-card mb-3 card">
		<div class="card-body">
			<form method="POST" action="{{ route('drivers.store') }}" enctype="multipart/form-data" class="needs-validation" novalidate>
				@csrf
				<x-form-input
					name="name"
					type="text"
					label="{{ __('Name') }}"
					required
				/>
				<x-form-input
					name="email"
					type="text"
					label="{{ __('Email') }}"
				/>
				<x-form-input
					name="phone"
					type="text"
					label="{{ __('Phone') }}"
					required
				/>
				<x-form-input
					name="password"
					type="password"
					label="{{ __('Password') }}"
					required
				/>
				<x-form-input
					name="password_confirmation"
					type="password"
					label="{{ __('Confirm Password') }}"
					required
				/>
			<x-form-select
				name="status"
				type="select"
				:options="$driverStatus"
				label="{{ __('Status') }}"
				required
			/>

			<div class="mb-3">
				<label for="image" class="form-label">{{ __('Profile Image') }}</label>
				<input type="file" class="form-control @error('image') is-invalid @enderror"
					   id="image" name="image" accept="image/jpeg,image/png,image/jpg,image/webp">
				@error('image')
					<div class="invalid-feedback">{{ $message }}</div>
				@enderror
				<div id="imagePreviewWrapper" class="mt-2 d-none">
					<img id="imagePreview" src="#" alt="{{ __('Profile Image Preview') }}"
						 class="rounded" style="height: 120px; width: 120px; object-fit: cover; border: 1px solid #dee2e6;">
				</div>
			</div>

			@if($requiredDocumentTypes->isNotEmpty())
					<hr class="my-4">
					<h5 class="mb-3">{{ __('Required Documents') }}</h5>
					@foreach($requiredDocumentTypes as $docType)
						<div class="mb-3">
							<label for="document_types_{{ $docType->id }}" class="form-label">{{ $docType->name }} <span class="text-danger">*</span></label>
							<input type="file" class="form-control @error('document_types.'.$docType->id) is-invalid @enderror"
								   id="document_types_{{ $docType->id }}" name="document_types[{{ $docType->id }}]" accept="image/*" required>
							@error('document_types.'.$docType->id)
								<div class="invalid-feedback">{{ $message }}</div>
							@enderror
						</div>
					@endforeach
				@endif

			<button type="submit" class="btn btn-primary btn-sm me-1">{{ __('Add') }}</button>
		</form>
	</div>
</div>
</div>
@endsection
@push('scripts')
<script>
	document.getElementById('image').addEventListener('change', function () {
		const wrapper = document.getElementById('imagePreviewWrapper');
		const preview = document.getElementById('imagePreview');
		if (this.files && this.files[0]) {
			const reader = new FileReader();
			reader.onload = function (e) {
				preview.src = e.target.result;
				wrapper.classList.remove('d-none');
			};
			reader.readAsDataURL(this.files[0]);
		} else {
			wrapper.classList.add('d-none');
			preview.src = '#';
		}
	});
</script>
@endpush
