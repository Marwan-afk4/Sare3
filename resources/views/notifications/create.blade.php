@extends('layouts.app')
@php
	$currentPage = 'notifications';
@endphp
@section('title', __('Create Notification'))
@section('content')
<div class="container">
	<h1>{{ __('Create Notification') }}</h1>
	<div class="mb-3">
		<a href="{{ route('notifications.index') }}" class="btn btn-secondary btn-sm me-1"> <i class="fa fa-arrow-right"></i> {{__('Back to')}} {{__('Notifications')}}</a>
	</div>
	<div class="main-card mb-3 card">
		<div class="card-body">
			<form method='POST' action='{{ route('notifications.store') }}' class="needs-validation" novalidate>
				@csrf
				<x-form-select
					name="type"
					type="select"
					label="{{__('Type')}}"
					:options="$types"
                    required
				/>
				<x-form-input
                    name="data[title]"
                    type="text"
                    label="{{ __('Title') }}"
                    required
                />

                <x-form-textarea
                    name="data[body]"
                    type="text"
                    label="{{ __('Message') }}"
                    required
                />
				<x-form-select
					name="driver_id"
					type="select"
					label="{{__('Driver')}}"
					:selected="$notification->driver_id ?? ''"
					:options="$drivers"
				/>
				<button type='submit' class="btn btn-primary btn-sm me-1">{{ __('Add') }}</button>
			</form>
		</div>
	</div>
</div>@endsection
