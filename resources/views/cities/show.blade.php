@extends('layouts.app')
@php
	$currentPage = 'cities';
@endphp
@section('title', $city->name)
@section('content')
<div class="container-fluid">
	<h1>{{ $city->name }}</h1>
	<div class="mb-3">
		<a href="{{ route('cities.index') }}" class="btn btn-secondary btn-sm me-1"> <i class="fa fa-arrow-right"></i> {{__('Back to')}} {{__('Cities')}}</a>
		<a href='{{ route('cities.edit', $city) }}' class="btn btn-warning btn-sm me-1">{{ __('Edit') }} <i class="fa fa-edit"></i></a>
	</div>
	<div class="card">
		<div class="card-body">
			<ul class="list-group list-group-flush">
				<li class="list-group-item">
					<strong>{{ __("Id") }}:</strong> {{ $city->id }}
				</li>
				<li class="list-group-item">
					<strong>{{ __("Name") }}:</strong> {{ $city->name }}
				</li>
				<li class="list-group-item">
					<strong>{{ __("Status") }}:</strong> @if($city->status == 'active') <span class="badge bg-success">{{__('Active')}}</span> @else <span class="badge bg-danger">{{__('Inactive')}}</span> @endif
				</li>
				<li class="list-group-item">
					<strong>{{ __("Created At") }}:</strong> {{ $city->created_at->diffForHumans() }}
				</li>
				<li class="list-group-item">
					<strong>{{ __("Updated At") }}:</strong> {{ $city->updated_at->diffForHumans() }}
				</li>
			</ul>
		</div>
	</div>
	<div class="mt-3">
		{{-- <form method='POST' action='{{ route('cities.destroy', $city) }}' onsubmit='return confirm("Are you sure you want to delete this item?")'>
			<input type='hidden' name='_method' value='DELETE'>
			<button type='submit' class="btn btn-square btn-danger">{{ __('Delete') }}</button>
		</form> --}}
	</div>
</div>
@endsection