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
				<button type='submit' class="btn btn-warning btn-sm me-1">{{ __('Save') }}</button>
			</form>
		</div>
	</div>
</div>
@endsection
