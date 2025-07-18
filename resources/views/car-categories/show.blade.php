@extends('layouts.app')
@php
	$currentPage = 'car-categories';
@endphp
@section('title', $carCategory->name)
@section('content')
<div class="container-fluid">
	<h1>{{ $carCategory->name }}</h1>
	<div class="mb-3">
		<a href="{{ route('car-categories.index') }}" class="btn btn-secondary btn-sm me-1"> <i class="fa fa-arrow-right"></i> {{__('Back to')}} {{__('Car Categories')}}</a>
		{{-- <a href='{{ route('car-categories.edit', $carCategory) }}' class="btn btn-warning btn-sm me-1">{{ __('Edit') }} <i class="fa fa-edit"></i></a> --}}
	</div>
	<div class="card">
		<div class="card-body">
			<ul class="list-group list-group-flush">
				<li class="list-group-item">
					<strong>{{ __("Id") }}:</strong> {{ $carCategory->id }}
				</li>
				<li class="list-group-item">
					<strong>{{ __("Name") }}:</strong> {{ $carCategory->name }}
				</li>
				<li class="list-group-item">
					<strong>{{ __("Description") }}:</strong> {{ $carCategory->description }}
				</li>
				<li class="list-group-item">
					<strong>{{ __("Base Price") }}:</strong> {{ $carCategory->base_price }}
				</li>
				<li class="list-group-item">
					<strong>{{ __("Price Per Km") }}:</strong> {{ $carCategory->price_per_km }}
				</li>
				<li class="list-group-item">
					<strong>{{ __("Price Per Time") }}:</strong> {{ $carCategory->price_per_time }}
				</li>
				<li class="list-group-item">
					<strong>{{ __("Created At") }}:</strong> {{ $carCategory->created_at ? $carCategory->created_at->diffForHumans() : '-' }}
				</li>
				<li class="list-group-item">
					<strong>{{ __("Updated At") }}:</strong> {{ $carCategory->updated_at ? $carCategory->updated_at->diffForHumans() : '-'}}
				</li>
			</ul>
		</div>
        <div>
            <div class="card-footer">
                <a href='{{ route('car-categories.edit', $carCategory) }}' class="btn btn-warning btn-sm me-1">{{ __('Edit') }} <i class="fa fa-edit"></i></a>
            </div>
        </div>
	</div>
</div>
@endsection
