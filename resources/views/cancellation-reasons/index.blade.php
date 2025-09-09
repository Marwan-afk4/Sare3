@extends('layouts.app')
@php
	$currentPage = 'cancellation-reasons';
@endphp
@section('title', __('Cancellation Reasons'))
@section('content')
<div class="container-fluid">
	<h1 class="mb-3">{{__('Cancellation Reasons')}}</h1>
	<div class="mb-3">
		<a href="{{ route('cancellation-reasons.create') }}" class="btn btn-primary btn-sm me-1">{{__('Create Cancellation Reason')}} <i class="fa fa-plus"></i></a>
	</div>
	<div class='main-card mb-3 card'>
		<div class='card-body'>
			<table class="mb-0 table table-hover">
				<tr>
					<th>
						<a href="{{ route('cancellation-reasons.index', ['sort' => 'id', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Id") }}
							@if($sortField === 'id')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th>
						<a href="{{ route('cancellation-reasons.index', ['sort' => 'reason', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Reason") }}
							@if($sortField === 'reason')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th>
						<a href="{{ route('cancellation-reasons.index', ['sort' => 'type', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Type") }}
							@if($sortField === 'type')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th>
						<a href="{{ route('cancellation-reasons.index', ['sort' => 'is_active', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Is Active") }}
							@if($sortField === 'is_active')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th>
						<a href="{{ route('cancellation-reasons.index', ['sort' => 'created_at', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Created At") }}
							@if($sortField === 'created_at')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th class="text-center">{{ __('Actions') }}</th>
				</tr>
				@foreach($cancellationReasons as $cancellationReason)
				<tr>
					<td>{{ $cancellationReason->id }}</td>
					<td>{{ $cancellationReason->reason }}</td>
					<td>{!! $cancellationReason->type->badge() !!}</td>
					<td>
                        @if($cancellationReason->is_active)
                            <span class="text-success">
                                <i class="fa fa-check-circle"></i> {{ __('Yes') }}
                            </span>
                        @else
                            <span class="text-danger">
                                <i class="fa fa-times-circle"></i> {{ __('No') }}
                            </span>
                        @endif
                    </td>
					<td>{{ $cancellationReason->created_at?->diffForHumans() ?? '-' }}</td>
					<td class="text-center">
						<a href='{{ route('cancellation-reasons.show', $cancellationReason) }}' class="btn btn-subtle-primary btn-sm me-1">{{ __("Details") }} <i class="fa fa-eye"></i></a>
						<form action="{{ route('cancellation-reasons.destroy', $cancellationReason) }}" method="POST" class="d-inline"
                            onsubmit="return confirm('{{ __('Are you sure you want to delete this cancellation reason?') }}')">
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
			{{ $cancellationReasons->links('pagination::custom') }}
		</div>
	</div>
</div>
@endsection
