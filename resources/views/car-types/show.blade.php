@extends('layouts.app')
@php
	$currentPage = 'car-types';
@endphp
@section('title', $carType->name)
@section('content')
<div class="container-fluid">
	<h1>{{ $carType->name }}</h1>
	<div class="mb-3">
		<a href="{{ route('car-types.index') }}" class="btn btn-secondary btn-sm me-1"> <i class="fa fa-arrow-right"></i> {{__('Back to')}} {{__('Car Types')}}</a>
		<a href='{{ route('car-types.edit', $carType) }}' class="btn btn-warning btn-sm me-1">{{ __('Edit') }} <i class="fa fa-edit"></i></a>
	</div>
	<div class="card">
		<div class="card-body">
			<ul class="list-group list-group-flush">
				<li class="list-group-item">
					<strong>{{ __("Id") }}:</strong> {{ $carType->id }}
				</li>
				<li class="list-group-item">
					<strong>{{ __("Car Category") }}:</strong> {{ $carType->carCategory?->name }}
				</li>
				<li class="list-group-item">
					<strong>{{ __("Type Name") }}:</strong> {{ $carType->type_name }}
				</li>
				<li class="list-group-item">
					<strong>{{ __("Description") }}:</strong> {{ $carType->description }}
				</li>
				<li class="list-group-item">
					<strong>{{ __("Created At") }}:</strong> {{ $carType->created_at }}
				</li>
				<li class="list-group-item">
					<strong>{{ __("Updated At") }}:</strong> {{ $carType->updated_at }}
				</li>
			</ul>
		</div>
	</div>
	<div class="mt-3">
		{{-- <form method='POST' action='{{ route('car-types.destroy', $carType) }}' onsubmit='return confirm("Are you sure you want to delete this item?")'>
			<input type='hidden' name='_method' value='DELETE'>
			<button type='submit' class="btn btn-square btn-danger">{{ __('Delete') }}</button>
		</form> --}}
	</div>
</div>
@endsection