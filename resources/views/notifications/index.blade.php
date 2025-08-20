@extends('layouts.app')
@php
	$currentPage = 'notifications';
@endphp
@section('title', __('Notifications'))
@section('content')
<div class="container-fluid">
	<h1 class="mb-3">{{__('Notifications')}}</h1>
	<div class="mb-3">
		<a href="{{ route('notifications.create') }}" class="btn btn-primary btn-sm me-1">{{__('Send Notification')}} <i class="fa fa-plus"></i></a>
	</div>
	<div class='main-card mb-3 card'>
		<div class='card-body'>
			<table class="mb-0 table table-hover">
				<tr>
					<th>
						<a href="{{ route('notifications.index', ['sort' => 'id', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Id") }}
							@if($sortField === 'id')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th>
						<a href="{{ route('notifications.index', ['sort' => 'type', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Type") }}
							@if($sortField === 'type')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th>
						<a href="{{ route('notifications.index', ['sort' => 'title', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Title") }}
							@if($sortField === 'title')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th>
						<a href="{{ route('notifications.index', ['sort' => 'message', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Message") }}
							@if($sortField === 'message')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th>
						<a href="{{ route('notifications.index', ['sort' => 'driver_id', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Driver") }}
							@if($sortField === 'driver_id')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th>
						<a href="{{ route('notifications.index', ['sort' => 'created_at', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Created At") }}
							@if($sortField === 'created_at')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th class="text-center">{{ __('Actions') }}</th>
				</tr>
				@foreach($notifications as $notification)
				<tr>
					<td>{{ $notification->id }}</td>
					<td>{!! $notification->type->badge() !!}</td>
					<td>{{ $notification->title }}</td>
					<td>{{ $notification->message }}</td>
					<td>{{ $notification->driver?->name ??'-' }}</td>
					<td>{{ $notification->created_at?->diffForHumans() ??'-' }}</td>
					<td class="text-center">
						<a href='{{ route('notifications.show', $notification) }}' class="btn btn-subtle-primary btn-sm me-1">{{ __("Details") }} <i class="fa fa-eye"></i></a>
						{{-- <a href='{{ route('notifications.edit', $notification) }}' class="btn btn-subtle-warning btn-sm me-1">{{ __("Edit") }} <i class="fa fa-edit"></i></a> --}}
						{{-- <form method='POST' action='{{ route('notifications.destroy', $notification) }}' onsubmit='return confirm("Are you sure you want to delete this item?")'>
							<input type='hidden' name='_method' value='DELETE'>
							<button type='submit' class="btn btn-square btn-danger">{{ __('Delete') }}</button>
						</form> --}}
					</td>
				</tr>
				@endforeach
			</table>
			{{ $notifications->links('pagination::custom') }}
		</div>
	</div>
</div>
@endsection
