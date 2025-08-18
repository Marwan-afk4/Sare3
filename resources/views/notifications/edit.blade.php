@extends('layouts.app')
@php
	$currentPage = 'notifications';
@endphp
@section('title', __('Edit Notification'))
@section('content')
<div class="container">
	<h1>{{ __('Edit Notification') }}</h1>
	<div class="mb-3">
		<a href="{{ route('notifications.index') }}" class="btn btn-secondary btn-sm me-1"> <i class="fa fa-arrow-right"></i> {{__('Back to')}} {{__('Notifications')}}</a>
		<a href='{{ route('notifications.show', $notification) }}' class="btn btn-primary btn-sm me-1">{{ __("Details") }} <i class="fa fa-eye"></i></a>
	</div>
	<div class="main-card mb-3 card">
		<div class="card-body">
			<form method='POST' action='{{ route('notifications.update', $notification->id) }}' class="needs-validation" novalidate>
				@csrf
				@method('PUT')
				<x-form-input 
					name="type"
					type="text"
					label="{{__('Type')}}"
					:value="$notification->type ?? ''"
					required
				/>
				<x-form-input 
					name="title"
					type="text"
					label="{{__('Title')}}"
					:value="$notification->title ?? ''"
					required
				/>
				<x-form-input 
					name="message"
					type="text"
					label="{{__('Message')}}"
					:value="$notification->message ?? ''"
					required
				/>
				<x-form-select 
					name="driver_id"
					type="select"
					label="{{__('Driver')}}"
					:selected="$notification->driver_id ?? ''"
					:options="$drivers"
				/>
				<button type='submit' class="btn btn-warning btn-sm me-1">{{ __('Save') }}</button>
			</form>
		</div>
	</div>
</div>
@endsection