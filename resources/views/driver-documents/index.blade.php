@extends('layouts.app')
@php
	$currentPage = 'driver-documents';
@endphp
@section('title', __('Driver Documents'))
@section('content')
<div class="container-fluid">
	<h1 class="mb-3">{{__('Driver Documents')}}</h1>
	<div class="mb-3">
		<a href="{{ route('driver-documents.create') }}" class="btn btn-primary btn-sm me-1">{{__('Create Driver Document')}} <i class="fa fa-plus"></i></a>
	</div>
	<div class='main-card mb-3 card'>
		<div class='card-body'>
			<table class="mb-0 table table-hover">
				<tr>
					<th>
						<a href="{{ route('driver-documents.index', ['sort' => 'id', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Id") }}
							@if($sortField === 'id')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th>
						<a href="{{ route('driver-documents.index', ['sort' => 'driver_id', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Driver") }}
							@if($sortField === 'driver_id')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th>
						<a href="{{ route('driver-documents.index', ['sort' => 'document_type_id', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Document Type") }}
							@if($sortField === 'document_type_id')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th>
						<a href="{{ route('driver-documents.index', ['sort' => 'image_path', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Image Path") }}
							@if($sortField === 'image_path')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th>
						<a href="{{ route('driver-documents.index', ['sort' => 'created_at', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Created At") }}
							@if($sortField === 'created_at')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th class="text-center">{{ __('Actions') }}</th>
				</tr>
				@foreach($driverDocuments as $driverDocument)
				<tr>
					<td>{{ $driverDocument->id }}</td>
					<td>{{ $driverDocument->driver?->name }}</td>
					<td>{{ $driverDocument->documentType?->name }}</td>
					<td>{{ $driverDocument->image_path }}</td>
					<td>{{ $driverDocument->created_at }}</td>
					<td class="text-center">
						<a href='{{ route('driver-documents.show', $driverDocument) }}' class="btn btn-subtle-primary btn-sm me-1">{{ __("Details") }} <i class="fa fa-eye"></i></a>
						<a href='{{ route('driver-documents.edit', $driverDocument) }}' class="btn btn-subtle-warning btn-sm me-1">{{ __("Edit") }} <i class="fa fa-edit"></i></a>
						{{-- <form method='POST' action='{{ route('driver-documents.destroy', $driverDocument) }}' onsubmit='return confirm("Are you sure you want to delete this item?")'>
							<input type='hidden' name='_method' value='DELETE'>
							<button type='submit' class="btn btn-square btn-danger">{{ __('Delete') }}</button>
						</form> --}}
					</td>
				</tr>
				@endforeach
			</table>
			{{-- {{ $driverDocuments->links('pagination::custom') }} --}}
		</div>
	</div>
</div>
@endsection
