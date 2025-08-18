@extends('layouts.app')
@php
	$currentPage = 'notifications';
@endphp
@section('title', $notification->name)
@section('content')
<div class="container-fluid">
	<h1>{{ $notification->id }}</h1>
	<div class="mb-3">
		<a href="{{ route('notifications.index') }}" class="btn btn-secondary btn-sm me-1"> <i class="fa fa-arrow-right"></i> {{__('Back to')}} {{__('Notifications')}}</a>
		{{-- <a href='{{ route('notifications.edit', $notification) }}' class="btn btn-warning btn-sm me-1">{{ __('Edit') }} <i class="fa fa-edit"></i></a> --}}
	</div>
	<div class="card">
		<div class="card-body">
			<ul class="list-group list-group-flush">
				<li class="list-group-item">
					<strong>{{ __("Id") }}:</strong> {{ $notification->id }}
				</li>
                <li class="list-group-item">
					<strong>{{ __("Sender") }}:</strong> {{ $notification->user?->name ?? '-' }}
				</li>
				<li class="list-group-item">
					<strong>{{ __("Type") }}:</strong> {{ $notification->type->label() }}
				</li>
				<li class="list-group-item">
					<strong>{{ __("Title") }}:</strong> {{ $notification->title }}
				</li>
				<li class="list-group-item">
					<strong>{{ __("Message") }}:</strong> {{ $notification->message }}
				</li>
				<li class="list-group-item">
					<strong>{{ __("Driver") }}:</strong> {{ $notification->driver?->name ?? '-' }}
				</li>
				<li class="list-group-item">
					<strong>{{ __("Created At") }}:</strong> {{ $notification->created_at?->diffForHumans() ?? '-' }}
				</li>
				<li class="list-group-item">
					<strong>{{ __("Updated At") }}:</strong> {{ $notification->updated_at?->diffForHumans() ?? '-' }}
				</li>
			</ul>
		</div>
	</div>
	<div class="mt-3">
		{{-- <form method='POST' action='{{ route('notifications.destroy', $notification) }}' onsubmit='return confirm("Are you sure you want to delete this item?")'>
			<input type='hidden' name='_method' value='DELETE'>
			<button type='submit' class="btn btn-square btn-danger">{{ __('Delete') }}</button>
		</form> --}}
	</div>
</div>
@endsection
