@extends('layouts.app')
@php
	$currentPage = 'otp-limits';
@endphp
@section('title', __('Otp Limits'))
@section('content')
<div class="container-fluid">
	<h1 class="mb-3">{{__('Otp Limits')}}</h1>
	<div class="mb-3">
		<a href="{{ route('otp-limits.create') }}" class="btn btn-primary btn-sm me-1">{{__('Create Otp Limit')}} <i class="fa fa-plus"></i></a>
	</div>
	<div class='main-card mb-3 card'>
		<div class='card-body'>
			<table class="mb-0 table table-hover">
				<tr>
					<th>
						<a href="{{ route('otp-limits.index', ['sort' => 'id', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Id") }}
							@if($sortField === 'id')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th>
						<a href="{{ route('otp-limits.index', ['sort' => 'type', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Type") }}
							@if($sortField === 'type')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th>
						<a href="{{ route('otp-limits.index', ['sort' => 'otp_limit', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Otp Limit") }}
							@if($sortField === 'otp_limit')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th>
						<a href="{{ route('otp-limits.index', ['sort' => 'created_at', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Created At") }}
							@if($sortField === 'created_at')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th class="text-center">{{ __('Actions') }}</th>
				</tr>
				@foreach($otpLimits as $otpLimit)
				<tr>
					<td>{{ $otpLimit->id }}</td>
					<td>{!! $otpLimit->type->badge() !!}</td>
					<td>{{ $otpLimit->otp_limit }}</td>
					<td>{{ $otpLimit->created_at?->diffForHumans() ?? '-' }}</td>
					<td class="text-center">
						<a href='{{ route('otp-limits.show', $otpLimit) }}' class="btn btn-subtle-primary btn-sm me-1">{{ __("Details") }} <i class="fa fa-eye"></i></a>
						{{-- <a href='{{ route('otp-limits.edit', $otpLimit) }}' class="btn btn-subtle-warning btn-sm me-1">{{ __("Edit") }} <i class="fa fa-edit"></i></a> --}}
						{{-- <form method='POST' action='{{ route('otp-limits.destroy', $otpLimit) }}' onsubmit='return confirm("Are you sure you want to delete this item?")'>
							<input type='hidden' name='_method' value='DELETE'>
							<button type='submit' class="btn btn-square btn-danger">{{ __('Delete') }}</button>
						</form> --}}
					</td>
				</tr>
				@endforeach
			</table>
			{{ $otpLimits->links('pagination::custom') }}
		</div>
	</div>
</div>
@endsection
