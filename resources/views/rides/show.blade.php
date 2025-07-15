@extends('layouts.app')
@php
	$currentPage = 'rides';
@endphp
@section('title', $ride->name)
@section('content')
<div class="container-fluid">
	<h1>{{ $ride->id }}</h1>
	<div class="mb-3">
		<a href="{{ route('rides.index') }}" class="btn btn-secondary btn-sm me-1"> <i class="fa fa-arrow-right"></i> {{__('Back to')}} {{__('Rides')}}</a>
		{{-- <a href='{{ route('rides.edit', $ride) }}' class="btn btn-warning btn-sm me-1">{{ __('Edit') }} <i class="fa fa-edit"></i></a> --}}
	</div>
	<div class="card">
		<div class="card-body">
			<ul class="list-group list-group-flush">
				<li class="list-group-item">
					<strong>{{ __("Id") }}:</strong> {{ $ride->id }}
				</li>
				<li class="list-group-item">
					<strong>{{ __("User") }}:</strong> {{ $ride->user?->name }}
				</li>
				<li class="list-group-item">
					<strong>{{ __("Driver") }}:</strong> {{ $ride->driver?->name }}
				</li>
				<li class="list-group-item">
					<strong>{{ __("Car Category") }}:</strong> {{ $ride->carCategory?->name }}
				</li>
				<li class="list-group-item">
					<strong>{{ __("Pickup Address") }}:</strong> {{ $ride->pickup_address ?? '-' }}
				</li>
				<li class="list-group-item">
					<strong>{{ __("Dropoff Address") }}:</strong> {{ $ride->dropoff_address ?? '-' }}
				</li>
				<li class="list-group-item">
					<strong>{{ __("Estimated Km") }}:</strong> {{ $ride->estimated_km }}
				</li>
				<li class="list-group-item">
					<strong>{{ __("Estimated Time In Minutes") }}:</strong> {{ $ride->estimated_time }}
				</li>
				<li class="list-group-item">
					<strong>{{ __("Initial Price") }}:</strong> {{ $ride->calculated_initial_price }}
				</li>
				<li class="list-group-item">
					<strong>{{ __("Final Price") }}:</strong> {{ $ride->calculated_final_price }}
				</li>
				<li class="list-group-item">
					<strong>{{ __("Time Taken") }}:</strong> {{ $ride->time_taken }}
				</li>
                <li class="list-group-item">
					<strong>{{ __("Status") }}:</strong> <span class="badge badge-phoenix fs-10" style="background-color: #{{ $ride->status->color() }}; color: #{{ $ride->status->textColor() }};">
                        <span class="badge-label m-1">{{ $ride->status->label() }}</span>
				</li>
				<li class="list-group-item">
					<strong>{{ __("Created At") }}:</strong> {{ $ride->created_at }}
				</li>

			</ul>
		</div>
	</div>
	<div class="mt-3">
		{{-- <form method='POST' action='{{ route('rides.destroy', $ride) }}' onsubmit='return confirm("Are you sure you want to delete this item?")'>
			<input type='hidden' name='_method' value='DELETE'>
			<button type='submit' class="btn btn-square btn-danger">{{ __('Delete') }}</button>
		</form> --}}
	</div>
</div>
@endsection
