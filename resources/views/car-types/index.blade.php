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
						<a href="{{ route('car-types.index', ['sort' => 'car_model_id', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Car Model") }}
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
                        @if($carType->carModel)
                            <a href="{{ route('car-models.show', $carType->carModel) }}">{{ $carType->carModel?->name ?? '-' }}</a>
                        @endif
                    </td>
					<td>{{ $carType->type_name }}</td>
					<td>{{ $carType->description }}</td>
					<td>{{ $carType->created_at }}</td>
					<td class="text-center">
						<a href='{{ route('car-types.show', $carType) }}' class="btn btn-subtle-primary btn-sm me-1">{{ __("Details") }} <i class="fa fa-eye"></i></a>
						{{-- <a href='{{ route('car-types.edit', $carType) }}' class="btn btn-subtle-warning btn-sm me-1">{{ __("Edit") }} <i class="fa fa-edit"></i></a> --}}
						{{-- <form method='POST' action='{{ route('car-types.destroy', $carType) }}' onsubmit='return confirm("Are you sure you want to delete this item?")'>
							<input type='hidden' name='_method' value='DELETE'>
							<button type='submit' class="btn btn-square btn-danger">{{ __('Delete') }}</button>
						</form> --}}
					</td>
				</tr>
				@endforeach
			</table>
			{{ $carTypes->links('pagination::custom') }}
		</div>
	</div>
</div>
@endsection
