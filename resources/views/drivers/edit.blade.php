@use('Illuminate\Support\Facades\Storage')
@extends('layouts.app')
@php
	$currentPage = 'drivers';
@endphp
@section('title', __('Edit User'))
@section('content')
<div class="container">
	<h1>{{ __('Edit Driver') }}</h1>
	<div class="mb-3">
		<a href="{{ route('drivers.index') }}" class="btn btn-secondary btn-sm me-1"> <i class="fa fa-arrow-right"></i> {{__('Back to')}} {{__('Drivers')}}</a>
		<a href='{{ route('drivers.show', $driver) }}' class="btn btn-primary btn-sm me-1">{{ __("Details") }} <i class="fa fa-eye"></i></a>
	</div>
	@if (session('success'))
		<div class="alert alert-success">{{ session('success') }}</div>
	@endif
	<div class="main-card mb-3 card">
		<div class="card-body">
			@if ($errors->any() && ! old('_password_action'))
				<div class="alert alert-danger">
					<ul class="mb-0">
						@foreach ($errors->all() as $error)
							<li>{{ $error }}</li>
						@endforeach
					</ul>
				</div>
			@endif
			<form method='POST' enctype="multipart/form-data" action='{{ route('drivers.update', $driver->id) }}' id="driver-edit-form" novalidate>
				@csrf
				@method('PUT')
				<x-form-input
					name="name"
					type="text"
					label="{{__('Name')}}"
					:value="$driver->name ?? ''"
				/>
				<x-form-input
					name="email"
					type="text"
					label="{{__('Email')}}"
					:value="$driver->email ?? ''"
				/>
				<x-form-input
					name="phone"
					type="text"
					label="{{__('Phone')}}"
					:value="$driver->phone ?? ''"
				/>
                <x-form-input
                    name="wallet"
                    type="number"
                    step="0.01"
                    label="{{ __('Wallet') }}"
                    :value="$driver->wallet ?? ''"
                />
                <x-form-select
					name="status"
					type="select"
					label="{{__('Status')}}"
					:selected="$driver->status->value ?? ''"
                    :options="$driverStatus"
					required
				/>
                <x-form-select
                    name="activity"
                    type="select"
                    label="{{__('Activity')}}"
                    :selected="$driver->activity->value ?? ''"
                    :options="$diverActivityStatus"
                    required
                />
                <x-form-select
                    name="zone_id"
                    type="select"
                    label="{{__('Zone')}}"
                    :selected="$driver->zone_id ?? ''"
                    :options="$zones"
                    placeholder="{{__('Select Zone')}}"
                />
                <x-form-select
                    name="city_id"
                    type="select"
                    label="{{__('City')}}"
                    :selected="$driver->city_id ?? ''"
                    :options="$cities"
                    placeholder="{{__('Select City')}}"
                />

                <x-form-textarea
                    name="admin_notes"
                    label="{{ __('Admin Notes') }}"
                    :value="$driver->admin_notes"
                />
                <small class="text-muted d-block mb-3">{{ __('These notes are never shown to the driver or in the mobile app.') }}</small>

                <div class="mb-3">
                    <label for="image" class="form-label">{{ __('Profile Image') }}</label>
                    @if($driver->image)
                        <div class="mb-2">
                            <img id="imagePreview" src="{{ Storage::url($driver->image) }}"
                                 alt="{{ __('Current Profile Image') }}"
                                 class="rounded" style="height: 120px; width: 120px; object-fit: cover; border: 1px solid #dee2e6;">
                        </div>
                    @else
                        <div class="mb-2">
                            <img id="imagePreview" src="#" alt="{{ __('Profile Image Preview') }}"
                                 class="rounded d-none" style="height: 120px; width: 120px; object-fit: cover; border: 1px solid #dee2e6;">
                        </div>
                    @endif
                    <input type="file" class="form-control @error('image') is-invalid @enderror"
                           id="imageInput" name="image" accept="image/jpeg,image/png,image/jpg,image/webp">
                    @error('image')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">{{ __('Leave empty to keep the current image.') }}</small>
                </div>

				<button type='submit' class="btn btn-warning btn-sm me-1">{{ __('Save') }}</button>
			</form>
		</div>
	</div>

	<div class="main-card mb-3 card" id="password-section">
		<div class="card-body">
			<h5 class="mb-3">{{ __('Password') }}</h5>
			<p class="text-muted mb-3">
				@if($hasPassword)
					{{ __('This driver currently has a password set.') }}
				@else
					{{ __('This driver has no password and signs in using OTP only.') }}
				@endif
			</p>

			@if (old('_password_action') && $errors->any())
				<div class="alert alert-danger">
					<ul class="mb-0">
						@foreach ($errors->all() as $error)
							<li>{{ $error }}</li>
						@endforeach
					</ul>
				</div>
			@endif

			<form method="POST" action="{{ route('drivers.update', $driver) }}" id="driver-password-form" novalidate>
				@csrf
				@method('PUT')
				<input type="hidden" name="_password_action" id="password_action" value="set">

				@if($hasPassword)
					<div class="mb-3">
						<div class="form-check form-switch">
							<input
								class="form-check-input"
								type="checkbox"
								id="remove_password"
								value="1"
								{{ old('_password_action') === 'remove' ? 'checked' : '' }}
							>
							<label class="form-check-label" for="remove_password">
								{{ __('Remove password') }}
							</label>
						</div>
						<small class="text-muted">{{ __('Driver will sign in using OTP only.') }}</small>
					</div>
				@endif

				<div id="password-fields-wrapper">
					<div class="mb-3">
						<label for="driver_password" class="form-label">{{ __('New Password') }}</label>
						<input
							type="password"
							class="form-control @error('driver_password') is-invalid @enderror"
							id="driver_password"
							name="driver_password"
							autocomplete="new-password"
						>
						@error('driver_password')
							<div class="text-danger small mt-1">{{ $message }}</div>
						@enderror
					</div>

					<div class="mb-3">
						<label for="driver_password_confirmation" class="form-label">{{ __('Confirm New Password') }}</label>
						<input
							type="password"
							class="form-control @error('driver_password_confirmation') is-invalid @enderror"
							id="driver_password_confirmation"
							name="driver_password_confirmation"
							autocomplete="new-password"
						>
						@error('driver_password_confirmation')
							<div class="text-danger small mt-1">{{ $message }}</div>
						@enderror
					</div>
				</div>

				<button type="submit" class="btn btn-primary btn-sm">{{ __('Update Password') }}</button>
			</form>
		</div>
	</div>
</div>
@endsection
@push('scripts')
<script>
    (function () {
        const passwordForm = document.getElementById('driver-password-form');
        const removePassword = document.getElementById('remove_password');
        const passwordWrapper = document.getElementById('password-fields-wrapper');
        const password = document.getElementById('driver_password');
        const passwordConfirmation = document.getElementById('driver_password_confirmation');
        const passwordAction = document.getElementById('password_action');

        function togglePasswordFields() {
            if (!removePassword || !passwordWrapper) {
                return;
            }

            const removing = removePassword.checked;
            passwordWrapper.classList.toggle('d-none', removing);

            if (removing) {
                password.value = '';
                passwordConfirmation.value = '';
            }
        }

        if (removePassword) {
            removePassword.addEventListener('change', togglePasswordFields);
            togglePasswordFields();
        }

        if (passwordForm && passwordAction) {
            passwordForm.addEventListener('submit', function () {
                if (removePassword && removePassword.checked) {
                    passwordAction.value = 'remove';
                } else {
                    passwordAction.value = 'set';
                }
            });
        }

        @if (old('_password_action'))
        document.getElementById('password-section')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        @endif
    })();

    document.getElementById('imageInput').addEventListener('change', function () {
        const preview = document.getElementById('imagePreview');
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function (e) {
                preview.src = e.target.result;
                preview.classList.remove('d-none');
            };
            reader.readAsDataURL(this.files[0]);
        }
    });
</script>
@endpush
