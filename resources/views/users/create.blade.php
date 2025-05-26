@extends('layouts.app')
@php
	$currentPage = 'users';
@endphp
@section('title', __('Create User'))
@section('content')
<div class="container">
	<h1>{{ __('Create User') }}</h1>
	<div class="mb-3">
		<a wire:navigate href="{{ route('users.index') }}" class="btn btn-secondary btn-sm me-1"> <i class="fa fa-arrow-right"></i> {{__('Back to')}} {{__('Users')}}</a>
	</div>
	<div class="main-card mb-3 card">
		<div class="card-body">
			<form method='POST' action='{{ route('users.store') }}' class="needs-validation" novalidate>
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
					name="phone"
					type="text"
					label="{{__('Phone')}}"
					required
				/>
				{{-- <x-form-input
					name="image"
					type="text"
					label="{{__('Image')}}"
				/> --}}
				{{-- <x-form-input
					name="activity"
					type="text"
					label="{{__('Activity')}}"
					required
				/>
				<x-form-input
					name="wallet"
					type="number"
					label="{{__('Wallet')}}"
				/>
				<x-form-input
					name="role"
					type="text"
					label="{{__('Role')}}"
					required
				/> --}}
				<button type='submit' class="btn btn-primary btn-sm me-1">{{ __('Add') }}</button>
			</form>
		</div>
	</div>
</div>@endsection
