@extends('layouts.app')
@php
	$currentPage = 'car-categories';
@endphp
@section('title', __('Car Categories'))
@section('content')
<div class="container-fluid">
	<h1 class="mb-3">{{__('Car Categories')}}</h1>
	<div class="mb-3">
		<a href="{{ route('car-categories.create') }}" class="btn btn-primary btn-sm me-1">{{__('Create Car Category')}} <i class="fa fa-plus"></i></a>
	</div>
	<div class='main-card mb-3 card'>
		<div class='card-body'>
			<table class="mb-0 table table-hover">
				<tr>
					<th>
						<a href="{{ route('car-categories.index', ['sort' => 'icon', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Icon") }}
							@if($sortField === 'icon')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th>
						<a href="{{ route('car-categories.index', ['sort' => 'name', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Name") }}
							@if($sortField === 'name')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th>
						<a href="{{ route('car-categories.index', ['sort' => 'description', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Description") }}
							@if($sortField === 'description')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					{{-- <th>
						<a href="{{ route('car-categories.index', ['sort' => 'icon', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Icon") }}
							@if($sortField === 'icon')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th> --}}
					{{-- <th>
						<a href="{{ route('car-categories.index', ['sort' => 'base_price', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Base Price") }}
							@if($sortField === 'base_price')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th>
						<a href="{{ route('car-categories.index', ['sort' => 'price_per_km', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Price Per Km") }}
							@if($sortField === 'price_per_km')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th>
						<a href="{{ route('car-categories.index', ['sort' => 'price_per_time', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Price Per Time") }}
							@if($sortField === 'price_per_time')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th> --}}
					<th>
						<a href="{{ route('car-categories.index', ['sort' => 'created_at', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Created At") }}
							@if($sortField === 'created_at')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th class="text-center">{{ __('Actions') }}</th>
				</tr>
				@foreach($carCategories as $carCategory)
				<tr>
					<td>
                        @if ($carCategory->icon)
                            <img src="{{ $carCategory->icon_url }}" style="width: 100px;">
                        @endif
                    </td>
					<td>{{ $carCategory->name }}</td>
					<td>{{ $carCategory->description }}</td>
					{{-- <td>{{ $carCategory->base_price }}</td>
					<td>{{ $carCategory->price_per_km }}</td>
					<td>{{ $carCategory->price_per_time }}</td> --}}
					<td>{{ $carCategory->created_at ? $carCategory->created_at->diffForHumans() : '-' }}</td>
					<td class="text-center">
						<a href='{{ route('car-categories.show', $carCategory) }}' class="btn btn-subtle-primary btn-sm me-1">{{ __("Details") }} <i class="fa fa-eye"></i></a>
                        <form action="{{ route('car-categories.destroy', $carCategory) }}" method="POST" class="d-inline"
                            onsubmit="return confirm('{{ __('Are you sure you want to delete this car category?') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-subtle-danger btn-sm">
                                {{ __('Delete') }} <i class="fa fa-trash"></i>
                            </button>
                        </form>
						{{-- <a href='{{ route('car-categories.edit', $carCategory) }}' class="btn btn-subtle-warning btn-sm me-1">{{ __("Edit") }} <i class="fa fa-edit"></i></a> --}}
						{{-- <form method='POST' action='{{ route('car-categories.destroy', $carCategory) }}' onsubmit='return confirm("Are you sure you want to delete this item?")'>
							<input type='hidden' name='_method' value='DELETE'>
							<button type='submit' class="btn btn-square btn-danger">{{ __('Delete') }}</button>
						</form> --}}
					</td>
				</tr>
				@endforeach
			</table>
			{{ $carCategories->links('pagination::custom') }}
		</div>
	</div>
</div>
@endsection
