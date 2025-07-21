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
			<form method='POST' enctype="multipart/form-data" action='{{ route('drivers.update', $driver->id) }}' class="needs-validation" novalidate>
				@csrf
				@method('PUT')
				<x-form-input
					name="name"
					type="text"
					label="{{__('Name')}}"
					:value="$driver->name ?? ''"
					disabled
				/>
				<x-form-input
					name="email"
					type="text"
					label="{{__('Email')}}"
					:value="$driver->email ?? ''"
                    disabled
				/>
				<x-form-input
					name="phone"
					type="text"
					label="{{__('Phone')}}"
					:value="$driver->phone ?? ''"
					disabled
				/>
				<x-form-select
					name="status"
					type="select"
                    :options="['approved' => __('Approved'), 'rejected' => __('Rejected')]"
					label="{{__('Status')}}"
					:value="$driver->status ?? ''"
					:selected="$driver->status->value ?? ''"
                    :options="$driverStatus"
					required
				/>
				<button type='submit' class="btn btn-warning btn-sm me-1">{{ __('Save') }}</button>
			</form>
		</div>
	</div>
</div>
@endsection
