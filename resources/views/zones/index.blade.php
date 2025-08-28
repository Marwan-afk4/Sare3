@extends('layouts.app')
@php
	$currentPage = 'zones';
@endphp
@section('title', __('Zones'))
@section('content')
<div class="container-fluid">
	<h1 class="mb-3">{{__('Zones')}}</h1>
	<div class="mb-3">
		<a href="{{ route('zones.create') }}" class="btn btn-primary btn-sm me-1">{{__('Create Zone')}} <i class="fa fa-plus"></i></a>
	</div>
	<div class='main-card mb-3 card'>
		<div class='card-body'>
			<table class="mb-0 table table-hover">
				<tr>
					<th>
						<a href="{{ route('zones.index', ['sort' => 'id', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Id") }}
							@if($sortField === 'id')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th>
						<a href="{{ route('zones.index', ['sort' => 'name', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Name") }}
							@if($sortField === 'name')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th>
						<a href="{{ route('zones.index', ['sort' => 'from_lat', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("From Lat") }}
							@if($sortField === 'from_lat')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th>
						<a href="{{ route('zones.index', ['sort' => 'from_lng', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("From Lng") }}
							@if($sortField === 'from_lng')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th>
						<a href="{{ route('zones.index', ['sort' => 'to_lat', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("To Lat") }}
							@if($sortField === 'to_lat')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th>
						<a href="{{ route('zones.index', ['sort' => 'to_lng', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("To Lng") }}
							@if($sortField === 'to_lng')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th>{{ __("Zone Type") }}</th>
					<th>
						<a href="{{ route('zones.index', ['sort' => 'created_at', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Created At") }}
							@if($sortField === 'created_at')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th class="text-center">{{ __('Actions') }}</th>
				</tr>
				@foreach($zones as $zone)
				<tr>
					<td>{{ $zone->id }}</td>
					<td>{{ $zone->name }}</td>
					<td>{{ $zone->from_lat }}</td>
					<td>{{ $zone->from_lng }}</td>
					<td>{{ $zone->to_lat }}</td>
					<td>{{ $zone->to_lng }}</td>
					<td>
						@if(is_array($zone->polygon_coordinates))
                            <span class="badge bg-success">{{ __('Polygon') }}</span>
                            <small class="text-muted d-block">{{ count($zone->polygon_coordinates) }} {{ __('points') }}</small>
                        @else
                            <span class="badge bg-secondary">{{ __('Rectangle') }}</span>
                        @endif

					</td>
					<td>{{ $zone->created_at?->diffForHumans()??'-' }}</td>
					<td class="text-center">
						<a href='{{ route('zones.show', $zone) }}' class="btn btn-subtle-primary btn-sm me-1">{{ __("Details") }} <i class="fa fa-eye"></i></a>
						{{-- <a href='{{ route('zones.edit', $zone) }}' class="btn btn-subtle-warning btn-sm me-1">{{ __("Edit") }} <i class="fa fa-edit"></i></a> --}}
						{{-- <form method='POST' action='{{ route('zones.destroy', $zone) }}' onsubmit='return confirm("Are you sure you want to delete this item?")'>
							<input type='hidden' name='_method' value='DELETE'>
							<button type='submit' class="btn btn-square btn-danger">{{ __('Delete') }}</button>
						</form> --}}
					</td>
				</tr>
				@endforeach
			</table>
			{{ $zones->links('pagination::custom') }}
		</div>
	</div>
</div>
@endsection
