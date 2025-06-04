@extends('layouts.app')
@php
	$currentPage = 'driver-documents';
@endphp
@section('title', $driverDocument->name)
@section('content')
<div class="container-fluid">
	<h1>{{ $driverDocument->name }}</h1>
	<div class="mb-3">
		<a href="{{ route('driver-documents.index') }}" class="btn btn-secondary btn-sm me-1"> <i class="fa fa-arrow-right"></i> {{__('Back to')}} {{__('Driver Documents')}}</a>
		<a href='{{ route('driver-documents.edit', $driverDocument) }}' class="btn btn-warning btn-sm me-1">{{ __('Edit') }} <i class="fa fa-edit"></i></a>
	</div>
	<div class="card">
		<div class="card-body">
			<ul class="list-group list-group-flush">
				<li class="list-group-item">
					<strong>{{ __("Id") }}:</strong> {{ $driverDocument->id }}
				</li>
				<li class="list-group-item">
					<strong>{{ __("Driver") }}:</strong> {{ $driverDocument->driver?->name }}
				</li>
				<li class="list-group-item">
					<strong>{{ __("Document Type") }}:</strong> {{ $driverDocument->documentType?->name }}
				</li>
				<li class="list-group-item">
					<strong>{{ __("Image Path") }}:</strong> {{ $driverDocument->image_path }}
				</li>
				<li class="list-group-item">
					<strong>{{ __("Created At") }}:</strong> {{ $driverDocument->created_at }}
				</li>
				<li class="list-group-item">
					<strong>{{ __("Updated At") }}:</strong> {{ $driverDocument->updated_at }}
				</li>
			</ul>
		</div>
	</div>
	<div class="mt-3">
		{{-- <form method='POST' action='{{ route('driver-documents.destroy', $driverDocument) }}' onsubmit='return confirm("Are you sure you want to delete this item?")'>
			<input type='hidden' name='_method' value='DELETE'>
			<button type='submit' class="btn btn-square btn-danger">{{ __('Delete') }}</button>
		</form> --}}
	</div>
</div>
@endsection