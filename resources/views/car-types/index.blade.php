@extends('layouts.app')
@php
	$currentPage = 'car-types';
@endphp
@section('title', __('Car Types'))
@section('content')
<div class="container-fluid">
	<h1 class="mb-3">{{__('Car Types')}}</h1>
	<div class="mb-3">
		<a href="{{ route('car-types.create') }}" class="btn btn-primary btn-sm me-1">{{__('Create Car Type')}} <i class="fa fa-plus"></i></a>
	</div>
	<div class='main-card mb-3 card'>
		<div class='card-body'>
			<table class="mb-0 table table-hover">
				<tr>
					<th>
						<a href="{{ route('car-types.index', ['sort' => 'id', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Id") }}
							@if($sortField === 'id')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th>
						<a href="{{ route('car-types.index', ['sort' => 'car_category_id', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Car Category") }}
							@if($sortField === 'car_category_id')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th>
						<a href="{{ route('car-types.index', ['sort' => 'type_name', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Type Name") }}
							@if($sortField === 'type_name')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th>
						<a href="{{ route('car-types.index', ['sort' => 'description', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Description") }}
							@if($sortField === 'description')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th>
						<a href="{{ route('car-types.index', ['sort' => 'created_at', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Created At") }}
							@if($sortField === 'created_at')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th class="text-center">{{ __('Actions') }}</th>
				</tr>
				@foreach($carTypes as $carType)
				<tr>
					<td>{{ $carType->id }}</td>
					<td>
                        @if($carType->carCategory)
                            <a href="{{ route('car-categories.show', $carType->carCategory) }}">{{ $carType->carCategory?->name ?? '-' }}</a>
                        @endif
                    </td>
					<td>{{ $carType->type_name }}</td>
					<td>{{ $carType->description }}</td>
					<td>{{ $carType->created_at?->diffForHumans() ?? '-' }}</td>
					<td class="text-center">
						<a href='{{ route('car-types.show', $carType) }}' class="btn btn-subtle-primary btn-sm me-1">{{ __("Details") }} <i class="fa fa-eye"></i></a>
						<form action="{{ route('car-types.destroy', $carType) }}" method="POST" class="d-inline"
                            onsubmit="return confirm('{{ __('Are you sure you want to delete this car type?') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-subtle-danger btn-sm">
                                {{ __('Delete') }} <i class="fa fa-trash"></i>
                            </button>
                        </form>
					</td>
				</tr>
				@endforeach
			</table>
			{{ $carTypes->links('pagination::custom') }}
		</div>
	</div>
</div>
@endsection
