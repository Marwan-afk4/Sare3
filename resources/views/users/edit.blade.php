@extends('layouts.app')
@php
	$currentPage = 'users';
@endphp
@section('title', __('Edit User'))
@section('content')
<div class="container">
	<h1>{{ __('Edit User') }}</h1>
	<div class="mb-3">
		<a href="{{ route('users.index') }}" class="btn btn-secondary btn-sm me-1"> <i class="fa fa-arrow-right"></i> {{__('Back to')}} {{__('Users')}}</a>
		<a href='{{ route('users.show', $user) }}' class="btn btn-primary btn-sm me-1">{{ __("Details") }} <i class="fa fa-eye"></i></a>
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
			<form method='POST' action='{{ route('users.update', $user->id) }}' class="needs-validation" novalidate>
				@csrf
				@method('PUT')
				<x-form-input
					name="name"
					type="text"
					label="{{__('Name')}}"
					:value="$user->name ?? ''"
				/>
				<x-form-input
					name="email"
					type="text"
					label="{{__('Email')}}"
					:value="$user->email ?? ''"
				/>
				<x-form-input
					name="phone"
					type="text"
					label="{{__('Phone')}}"
					:value="$user->phone ?? ''"
				/>
                <x-form-input
                    name="wallet"
                    type="number"
                    label="{{__('Wallet')}}"
                    :value="$user->wallet ?? ''"
                />
				<x-form-select
					name="activity"
					type="select"
                    :options="['active' => __('Active'), 'inactive' => __('Inactive')]"
					label="{{__('Activity')}}"
					:selected="$user->activity->value ?? ''"
					required
				/>
				<button type='submit' class="btn btn-warning btn-sm me-1">{{ __('Save') }}</button>
			</form>
		</div>
	</div>

	<div class="main-card mb-3 card" id="password-section">
		<div class="card-body">
			<h5 class="mb-3">{{ __('Password') }}</h5>
			<p class="text-muted mb-3">
				@if($hasPassword)
					{{ __('This user currently has a password set.') }}
				@else
					{{ __('This user has no password and signs in using OTP only.') }}
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

			<form method="POST" action="{{ route('users.update', $user) }}" id="user-password-form" novalidate>
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
						<small class="text-muted">{{ __('User will sign in using OTP only.') }}</small>
					</div>
				@endif

				<div id="password-fields-wrapper">
					<div class="mb-3">
						<label for="user_password" class="form-label">{{ __('New Password') }}</label>
						<input
							type="password"
							class="form-control @error('user_password') is-invalid @enderror"
							id="user_password"
							name="user_password"
							autocomplete="new-password"
						>
						@error('user_password')
							<div class="text-danger small mt-1">{{ $message }}</div>
						@enderror
					</div>

					<div class="mb-3">
						<label for="user_password_confirmation" class="form-label">{{ __('Confirm New Password') }}</label>
						<input
							type="password"
							class="form-control @error('user_password_confirmation') is-invalid @enderror"
							id="user_password_confirmation"
							name="user_password_confirmation"
							autocomplete="new-password"
						>
						@error('user_password_confirmation')
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
        const passwordForm = document.getElementById('user-password-form');
        const removePassword = document.getElementById('remove_password');
        const passwordWrapper = document.getElementById('password-fields-wrapper');
        const password = document.getElementById('user_password');
        const passwordConfirmation = document.getElementById('user_password_confirmation');
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
</script>
@endpush
