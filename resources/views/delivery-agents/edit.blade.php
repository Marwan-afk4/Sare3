@use('Illuminate\Support\Facades\Storage')
@extends('layouts.app')
@php
    $currentPage = 'delivery-agents';
    $vehicle = $delivery_agent->riderVehicle;
    $documentFields = [
        'rider_image' => __('Rider photo'),
        'identity_image' => __('Identity'),
        'vehicle_image' => __('Vehicle'),
        'license_image' => __('License'),
    ];
@endphp
@section('title', __('Edit Delivery Agent'))
@section('content')
<div class="container">
    <h1>{{ __('Edit Delivery Agent') }}</h1>
    <div class="mb-3">
        <a href="{{ route('delivery-agents.index') }}" class="btn btn-secondary btn-sm me-1"><i class="fa fa-arrow-right"></i> {{ __('Back to') }} {{ __('Delivery Agents') }}</a>
        <a href="{{ route('delivery-agents.show', $delivery_agent) }}" class="btn btn-primary btn-sm me-1">{{ __('Details') }} <i class="fa fa-eye"></i></a>
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

            <form method="POST" enctype="multipart/form-data" action="{{ route('delivery-agents.update', $delivery_agent) }}" novalidate>
                @csrf
                @method('PUT')

                <x-form-input
                    name="name"
                    type="text"
                    label="{{ __('Name') }}"
                    :value="$delivery_agent->name ?? ''"
                    required
                />
                <x-form-input
                    name="email"
                    type="text"
                    label="{{ __('Email') }}"
                    :value="$delivery_agent->email ?? ''"
                />
                <x-form-input
                    name="phone"
                    type="text"
                    label="{{ __('Phone') }}"
                    :value="$delivery_agent->phone ?? ''"
                    required
                />
                <x-form-input
                    name="wallet"
                    type="number"
                    label="{{ __('Wallet') }}"
                    :value="(string) ($delivery_agent->wallet ?? '0')"
                    :attrs="['step' => '0.01', 'min' => '0']"
                    required
                />
                <x-form-select
                    name="status"
                    label="{{ __('Status') }}"
                    :selected="$delivery_agent->status?->value ?? ''"
                    :options="$agentStatuses"
                    required
                />
                <x-form-input
                    name="rejected_reason"
                    type="text"
                    label="{{ __('Rejected reason (optional)') }}"
                    :value="$delivery_agent->rejected_reason ?? ''"
                />
                <x-form-select
                    name="activity"
                    label="{{ __('Activity') }}"
                    :selected="$delivery_agent->activity?->value ?? ''"
                    :options="$activityStatuses"
                    required
                />
                <x-form-select
                    name="zone_id"
                    label="{{ __('Zone') }}"
                    :selected="$delivery_agent->zone_id ?? ''"
                    :options="$zones"
                />
                <x-form-select
                    name="city_id"
                    label="{{ __('City') }}"
                    :selected="$delivery_agent->city_id ?? ''"
                    :options="$cities"
                />
                <x-form-select
                    name="vehicle_type"
                    label="{{ __('Vehicle type') }}"
                    :selected="$vehicle?->type?->value ?? ''"
                    :options="$vehicleTypes"
                    required
                />
                <x-form-textarea
                    name="admin_notes"
                    label="{{ __('Admin Notes') }}"
                    :value="$delivery_agent->admin_notes"
                />
                <small class="text-muted d-block mb-3">{{ __('These notes are never shown to the rider or in the mobile app.') }}</small>

                <div class="mb-3">
                    <label for="image" class="form-label">{{ __('Profile Image') }}</label>
                    @if($delivery_agent->image)
                        <div class="mb-2">
                            <img id="imagePreview" src="{{ Storage::url($delivery_agent->image) }}"
                                 alt="{{ __('Profile Image') }}"
                                 class="rounded" style="height: 120px; width: 120px; object-fit: cover; border: 1px solid #dee2e6;">
                        </div>
                    @else
                        <div class="mb-2">
                            <img id="imagePreview" src="#" alt="{{ __('Profile Image') }}"
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

                <h5 class="mt-4 mb-3">{{ __('Documents') }}</h5>
                <div class="row">
                    @foreach($documentFields as $field => $label)
                        @php
                            $linkAttr = $field . '_link';
                            $current = $vehicle?->{$linkAttr};
                        @endphp
                        <div class="col-md-6 mb-3">
                            <label for="{{ $field }}" class="form-label">
                                {{ $label }}
                                @if($field === 'license_image')
                                    <span class="text-muted">({{ __('Required for motorcycles.') }})</span>
                                @endif
                            </label>
                            @if($current)
                                <div class="mb-2">
                                    <a href="{{ $current }}" target="_blank">
                                        <img src="{{ $current }}" alt="{{ $label }}" data-preview="{{ $field }}"
                                             class="rounded border" style="height: 110px; width: 110px; object-fit: cover;">
                                    </a>
                                </div>
                            @else
                                <div class="mb-2">
                                    <img src="#" alt="{{ $label }}" data-preview="{{ $field }}"
                                         class="rounded border d-none" style="height: 110px; width: 110px; object-fit: cover;">
                                </div>
                            @endif
                            <input type="file"
                                   class="form-control @error($field) is-invalid @enderror"
                                   id="{{ $field }}"
                                   name="{{ $field }}"
                                   accept="image/jpeg,image/png,image/jpg,image/webp"
                                   data-file-preview="{{ $field }}">
                            @error($field)
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">{{ __('Leave empty to keep the current image.') }}</small>
                        </div>
                    @endforeach
                </div>

                <button type="submit" class="btn btn-warning btn-sm me-1">{{ __('Save') }}</button>
            </form>
        </div>
    </div>

    <div class="main-card mb-3 card" id="password-section">
        <div class="card-body">
            <h5 class="mb-3">{{ __('Password') }}</h5>
            <p class="text-muted mb-3">
                @if($hasPassword)
                    {{ __('This rider currently has a password set.') }}
                @else
                    {{ __('This rider has no password and signs in using OTP only.') }}
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

            <form method="POST" action="{{ route('delivery-agents.update', $delivery_agent) }}" id="rider-password-form" novalidate>
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
                        <small class="text-muted">{{ __('Rider will sign in using OTP only.') }}</small>
                    </div>
                @endif

                <div id="password-fields-wrapper">
                    <div class="mb-3">
                        <label for="rider_password" class="form-label">{{ __('New Password') }}</label>
                        <input
                            type="password"
                            class="form-control @error('rider_password') is-invalid @enderror"
                            id="rider_password"
                            name="rider_password"
                            autocomplete="new-password"
                        >
                        @error('rider_password')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="rider_password_confirmation" class="form-label">{{ __('Confirm New Password') }}</label>
                        <input
                            type="password"
                            class="form-control"
                            id="rider_password_confirmation"
                            name="rider_password_confirmation"
                            autocomplete="new-password"
                        >
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
        const passwordForm = document.getElementById('rider-password-form');
        const removePassword = document.getElementById('remove_password');
        const passwordWrapper = document.getElementById('password-fields-wrapper');
        const password = document.getElementById('rider_password');
        const passwordConfirmation = document.getElementById('rider_password_confirmation');
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

        document.getElementById('imageInput')?.addEventListener('change', function () {
            const preview = document.getElementById('imagePreview');
            if (!preview || !this.files || !this.files[0]) {
                return;
            }

            const reader = new FileReader();
            reader.onload = function (e) {
                preview.src = e.target.result;
                preview.classList.remove('d-none');
            };
            reader.readAsDataURL(this.files[0]);
        });

        document.querySelectorAll('[data-file-preview]').forEach(function (input) {
            input.addEventListener('change', function () {
                const preview = document.querySelector('[data-preview="' + this.dataset.filePreview + '"]');
                if (!preview || !this.files || !this.files[0]) {
                    return;
                }

                const reader = new FileReader();
                reader.onload = function (e) {
                    preview.src = e.target.result;
                    preview.classList.remove('d-none');
                };
                reader.readAsDataURL(this.files[0]);
            });
        });

        @if (old('_password_action'))
        document.getElementById('password-section')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        @endif
    })();
</script>
@endpush
