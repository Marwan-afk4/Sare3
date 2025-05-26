@extends('layouts.app')
@php
	$currentPage = 'drivers';
@endphp
@section('title', __('Create Driver'))
@section('content')
<div class="container">
	<h1>{{ __('Create Driver') }}</h1>
	<div class="mb-3">
		<a wire:navigate href="{{ route('drivers.index') }}" class="btn btn-secondary btn-sm me-1"> <i class="fa fa-arrow-right"></i> {{__('Back to')}} {{__('Drivers')}}</a>
	</div>
	<div class="main-card mb-3 card">
		<div class="card-body">
			<form method='POST' action='{{ route('drivers.store') }}' class="needs-validation" novalidate>
				@csrf
				<x-form-input
					name="name"
					type="text"
					label="{{__('Name')}}"
					required
				/>
				<x-form-input
					name="email"
					type="text"
					label="{{__('Email')}}"
				/>
                <x-form-input
                    name="password"
                    type="password"
                    label="{{__('Password')}}"
                    required
                />
				<x-form-input
					name="phone"
					type="text"
					label="{{__('Phone')}}"
					required
				/>
				<button type='submit' class="btn btn-primary btn-sm me-1">{{ __('Add') }}</button>
			</form>
		</div>
	</div>
</div>@endsection
