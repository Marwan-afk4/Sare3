@extends('layouts.app')
@php
	$currentPage = 'cancellation-policies';
@endphp
@section('title', __('Cancellation Policies'))
@section('content')
<div class="container-fluid">
	<h1 class="mb-3">{{__('Cancellation Policies')}}</h1>
	<div class="mb-3">
		<a href="{{ route('cancellation-policies.create') }}" class="btn btn-primary btn-sm me-1">{{__('Create Cancellation Policy')}} <i class="fa fa-plus"></i></a>
	</div>
	<div class='main-card mb-3 card'>
		<div class='card-body'>
			<table class="mb-0 table table-hover">
				<tr>
					<th>
						<a href="{{ route('cancellation-policies.index', ['sort' => 'id', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Id") }}
							@if($sortField === 'id')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th>
						<a href="{{ route('cancellation-policies.index', ['sort' => 'name', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Name") }}
							@if($sortField === 'name')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th>
						<a href="{{ route('cancellation-policies.index', ['sort' => 'penalty_amount', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Penalty Amount") }}
							@if($sortField === 'penalty_amount')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th>
						<a href="{{ route('cancellation-policies.index', ['sort' => 'penalty_percent', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Penalty Percent") }}
							@if($sortField === 'penalty_percent')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					{{-- <th>
						<a href="{{ route('cancellation-policies.index', ['sort' => 'description', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Description") }}
							@if($sortField === 'description')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th> --}}
					<th>
						<a href="{{ route('cancellation-policies.index', ['sort' => 'status', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Status") }}
							@if($sortField === 'status')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th>
						<a href="{{ route('cancellation-policies.index', ['sort' => 'min_minutes', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Min Minutes") }}
							@if($sortField === 'min_minutes')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th>
						<a href="{{ route('cancellation-policies.index', ['sort' => 'max_minutes', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Max Minutes") }}
							@if($sortField === 'max_minutes')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
                    <th>
						<a href="{{ route('cancellation-policies.index', ['sort' => 'created_at', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Created At") }}
							@if($sortField === 'created_at')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th class="text-center">{{ __('Actions') }}</th>
				</tr>
				@foreach($cancellationPolicies as $cancellationPolicy)
				<tr>
					<td>{{ $cancellationPolicy->id }}</td>
					<td>{{ $cancellationPolicy->name }}</td>
					<td>{{ $cancellationPolicy->penalty_amount ?? '-' }}</td>
					<td>{{ $cancellationPolicy->penalty_percent ?? '-' }}</td>
					{{-- <td>{{ $cancellationPolicy->description }}</td> --}}
					<td>{!! $cancellationPolicy->status->badge() !!}</td>
					<td>{{ $cancellationPolicy->min_minutes }}</td>
					<td>{{ $cancellationPolicy->max_minutes }}</td>
					<td>{{ $cancellationPolicy->created_at->diffForHumans() ?? '-' }}</td>
					<td class="text-center">
						<a href='{{ route('cancellation-policies.show', $cancellationPolicy) }}' class="btn btn-subtle-primary btn-sm me-1">{{ __("Details") }} <i class="fa fa-eye"></i></a>
						<form action="{{ route('cancellation-policies.destroy', $cancellationPolicy) }}" method="POST" class="d-inline"
                            onsubmit="return confirm('{{ __('Are you sure you want to delete this cancellation policy?') }}')">
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
			{{ $cancellationPolicies->links('pagination::custom') }}
		</div>
	</div>
</div>
@endsection
