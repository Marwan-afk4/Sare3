@extends('layouts.app')
@php
	$currentPage = 'document-types';
@endphp
@section('title', __('Document Types'))
@section('content')
<div class="container-fluid">
	<h1 class="mb-3">{{__('Document Types')}}</h1>
	<div class="mb-3">
		<a href="{{ route('document-types.create') }}" class="btn btn-primary btn-sm me-1">{{__('Create Document Type')}} <i class="fa fa-plus"></i></a>
	</div>
	<div class='main-card mb-3 card'>
		<div class='card-body'>
			<table class="mb-0 table table-hover">
				<tr>
					<th>
						<a href="{{ route('document-types.index', ['sort' => 'id', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Id") }}
							@if($sortField === 'id')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th>
						<a href="{{ route('document-types.index', ['sort' => 'name', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Name") }}
							@if($sortField === 'name')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th>
						<a href="{{ route('document-types.index', ['sort' => 'is_required', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Is Required") }}
							@if($sortField === 'is_required')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th>
						<a href="{{ route('document-types.index', ['sort' => 'created_at', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Created At") }}
							@if($sortField === 'created_at')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th class="text-center">{{ __('Actions') }}</th>
				</tr>
				@foreach($documentTypes as $documentType)
				<tr>
					<td>{{ $documentType->id }}</td>
					<td>{{ $documentType->name }}</td>
                    <td>
                        @if($documentType->is_required)
                            <span class="badge bg-success text-white">{{ __('Active') }}</span>
                        @else
                            <span class="badge bg-danger text-white">{{ __('Inactive') }}</span>
                        @endif
                    </td>
					<td>{{ $documentType->created_at? $documentType->created_at->diffForHumans() : '-' }}</td>
					<td class="text-center">
						<a href='{{ route('document-types.show', $documentType) }}' class="btn btn-subtle-primary btn-sm me-1">{{ __("Details") }} <i class="fa fa-eye"></i></a>
						{{-- <a href='{{ route('document-types.edit', $documentType) }}' class="btn btn-subtle-warning btn-sm me-1">{{ __("Edit") }} <i class="fa fa-edit"></i></a> --}}
						{{-- <form method='POST' action='{{ route('document-types.destroy', $documentType) }}' onsubmit='return confirm("Are you sure you want to delete this item?")'>
							<input type='hidden' name='_method' value='DELETE'>
							<button type='submit' class="btn btn-square btn-danger">{{ __('Delete') }}</button>
						</form> --}}
					</td>
				</tr>
				@endforeach
			</table>
			{{ $documentTypes->links('pagination::custom') }}
		</div>
	</div>
</div>
@endsection
