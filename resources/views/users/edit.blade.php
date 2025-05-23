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
	<div class="main-card mb-3 card">
		<div class="card-body">
			<form method='POST' action='{{ route('users.update', $user->id) }}' class="needs-validation" novalidate>
				@csrf
				@method('PUT')
				<x-form-input 
					name="name"
					type="text"
					label="{{__('Name')}}"
					:value="$user->name ?? ''"
					required
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
					required
				/>
				<x-form-input 
					name="image"
					type="text"
					label="{{__('Image')}}"
					:value="$user->image ?? ''"
				/>
				<x-form-input 
					name="activity"
					type="text"
					label="{{__('Activity')}}"
					:value="$user->activity ?? ''"
					required
				/>
				<x-form-input 
					name="wallet"
					type="number"
					label="{{__('Wallet')}}"
					:value="$user->wallet ?? ''"
				/>
				<x-form-input 
					name="role"
					type="text"
					label="{{__('Role')}}"
					:value="$user->role ?? ''"
					required
				/>
				<button type='submit' class="btn btn-warning btn-sm me-1">{{ __('Save') }}</button>
			</form>
		</div>
	</div>
</div>
@endsection