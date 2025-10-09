@extends('layouts.app')
@php
	$currentPage = 'car-models';
@endphp
@section('title', __('Car Models'))
@section('content')
<div class="container-fluid">
	<h1 class="mb-3">{{__('Car Models')}}</h1>
	<div class="mb-3">
		<a href="{{ route('car-models.create') }}" class="btn btn-primary btn-sm me-1">{{__('Create Car Model')}} <i class="fa fa-plus"></i></a>
	</div>
	<div class='main-card mb-3 card'>
		<div class='card-body'>
			<table class="mb-0 table table-hover">
				<tr>
					<th>
						<a href="{{ route('car-models.index', ['sort' => 'id', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Id") }}
							@if($sortField === 'id')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th>
						<a href="{{ route('car-models.index', ['sort' => 'name', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Name") }}
							@if($sortField === 'name')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th>
						<a href="{{ route('car-models.index', ['sort' => 'created_at', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Created At") }}
							@if($sortField === 'created_at')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th class="text-center">{{ __('Actions') }}</th>
				</tr>
				@foreach($carModels as $carModel)
				<tr>
					<td>{{ $carModel->id }}</td>
					<td>{{ $carModel->name }}</td>
					<td>{{ $carModel->created_at?->diffForHumans() ?? '-' }}</td>
					<td class="text-center">
						<a href='{{ route('car-models.show', $carModel) }}' class="btn btn-subtle-primary btn-sm me-1">{{ __("Details") }} <i class="fa fa-eye"></i></a>
                        <form action="{{ route('car-models.destroy', $carModel) }}" method="POST" class="d-inline"
                            onsubmit="return confirm('{{ __('Are you sure you want to delete this car model?') }}')">
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
			{{ $carModels->links('pagination::custom') }}
		</div>
	</div>
</div>
@endsection
