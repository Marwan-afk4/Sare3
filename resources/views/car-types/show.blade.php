@extends('layouts.app')
@php
	$currentPage = 'car-types';
@endphp
@section('title', $carType->name)
@section('content')
<div class="container-fluid">
	<h1>{{ $carType->id }}</h1>
	<div class="mb-3">
		<a href="{{ route('car-types.index') }}" class="btn btn-secondary btn-sm me-1"> <i class="fa fa-arrow-right"></i> {{__('Back to')}} {{__('Car Types')}}</a>
		{{-- <a href='{{ route('car-types.edit', $carType) }}' class="btn btn-warning btn-sm me-1">{{ __('Edit') }} <i class="fa fa-edit"></i></a> --}}
	</div>
	<div class="card">
		<div class="card-body">
			<ul class="list-group list-group-flush">
				<li class="list-group-item">
					<strong>{{ __("Id") }}:</strong> {{ $carType->id }}
				</li>
				<li class="list-group-item">
					<strong>{{ __("Car Brand") }}:</strong> 
					@if($carType->carBrand)
						<span class="badge bg-success">{{ $carType->carBrand->name }}</span>
					@else
						-
					@endif
				</li>
				<li class="list-group-item">
					<strong>{{ __("Car Categories") }}:</strong> 
					@if($carType->carCategories->count() > 0)
						@foreach($carType->carCategories as $category)
							<span class="badge bg-primary">{{ $category->name }}</span>
						@endforeach
					@else
						-
					@endif
				</li>
				<li class="list-group-item">
					<strong>{{ __("Type Name") }}:</strong> {{ $carType->type_name }}
				</li>
				<li class="list-group-item">
					<strong>{{ __("Description") }}:</strong> {{ $carType->description }}
				</li>
				<li class="list-group-item">
					<strong>{{ __("Created At") }}:</strong> {{ $carType->created_at->diffForHumans() }}
				</li>
				<li class="list-group-item">
					<strong>{{ __("Updated At") }}:</strong> {{ $carType->updated_at->diffForHumans() }}
				</li>
			</ul>
		</div>
        <div>
            <div class="card-footer">
                <a href='{{ route('car-types.edit', $carType) }}' class="btn btn-warning btn-sm me-1">{{ __('Edit') }} <i class="fa fa-edit"></i></a>
            </div>
        </div>
	</div>
</div>
@endsection
