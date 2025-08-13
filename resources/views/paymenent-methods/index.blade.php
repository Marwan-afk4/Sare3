@extends('layouts.app')
@php
	$currentPage = 'paymenent-methods';
@endphp
@section('title', __('Paymenent Methods'))
@section('content')
<div class="container-fluid">
	<h1 class="mb-3">{{__('Paymenent Methods')}}</h1>
	{{-- <div class="mb-3">
		<a href="{{ route('paymenent-methods.create') }}" class="btn btn-primary btn-sm me-1">{{__('Create Paymenent Method')}} <i class="fa fa-plus"></i></a>
	</div> --}}
	<div class='main-card mb-3 card'>
		<div class='card-body'>
			<table class="mb-0 table table-hover">
				<tr>
					<th>
						<a href="{{ route('paymenent-methods.index', ['sort' => 'id', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Id") }}
							@if($sortField === 'id')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th>
						<a href="{{ route('paymenent-methods.index', ['sort' => 'name', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Name") }}
							@if($sortField === 'name')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th>
						<a href="{{ route('paymenent-methods.index', ['sort' => 'status', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Status") }}
							@if($sortField === 'status')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th>
						<a href="{{ route('paymenent-methods.index', ['sort' => 'created_at', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Created At") }}
							@if($sortField === 'created_at')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th class="text-center">{{ __('Actions') }}</th>
				</tr>
				@foreach($paymenentMethods as $paymenentMethod)
				<tr>
					<td>{{ $paymenentMethod->id }}</td>
					<td>{{ $paymenentMethod->name }}</td>
					<td>{!! $paymenentMethod->status->badge() !!}</td>
					<td>{{ $paymenentMethod->created_at->diffForHumans() ?? '-' }}</td>
					<td class="text-center">
						<a href='{{ route('paymenent-methods.show', $paymenentMethod) }}' class="btn btn-subtle-primary btn-sm me-1">{{ __("Details") }} <i class="fa fa-eye"></i></a>
						<a href='{{ route('paymenent-methods.edit', $paymenentMethod) }}'  class="btn btn-subtle-success btn-sm me-1">
                                    <i class="fa fa-check"></i> {{ __('Edit') }}
                                </a>
						{{-- <form method='POST' action='{{ route('paymenent-methods.destroy', $paymenentMethod) }}' onsubmit='return confirm("Are you sure you want to delete this item?")'>
							<input type='hidden' name='_method' value='DELETE'>
							<button type='submit' class="btn btn-square btn-danger">{{ __('Delete') }}</button>
						</form> --}}
					</td>
				</tr>
				@endforeach
			</table>
			{{ $paymenentMethods->links('pagination::custom') }}
		</div>
	</div>
</div>
@endsection
