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
	<div class="main-card mb-3 card">
		<div class="card-body">
			<form method='POST' enctype="multipart/form-data" action='{{ route('drivers.update', $driver->id) }}'  novalidate>
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
					name="password"
					type="password"
					label="{{ __('Password (Leave blank to keep current)') }}"
				/>
				<x-form-input
					name="password_confirmation"
					type="password"
					label="{{ __('Confirm Password') }}"
				/>
				@if($hasPassword)
					<div class="mb-3">
						<div class="form-check form-switch">
							<input
								class="form-check-input"
								type="checkbox"
								name="remove_password"
								id="remove_password"
								value="1"
								{{ old('remove_password') ? 'checked' : '' }}
							>
							<label class="form-check-label" for="remove_password">
								{{ __('Remove password') }}
							</label>
						</div>
						<small class="text-muted">{{ __('Driver will sign in using OTP only.') }}</small>
					</div>
				@endif
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
                    :options="['active' => __('Active'), 'inactive' => __('Inactive')]"
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
</div>
@endsection
@push('scripts')
<script>
    (function () {
        const removePassword = document.getElementById('remove_password');
        const password = document.getElementById('password');
        const passwordConfirmation = document.getElementById('password_confirmation');

        function togglePasswordFields() {
            if (!removePassword) {
                return;
            }

            const disabled = removePassword.checked;
            password.disabled = disabled;
            passwordConfirmation.disabled = disabled;

            if (disabled) {
                password.value = '';
                passwordConfirmation.value = '';
            }
        }

        if (removePassword) {
            removePassword.addEventListener('change', togglePasswordFields);
            togglePasswordFields();
        }
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
