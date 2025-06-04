@extends('layouts.app')
@php
	$currentPage = 'document-types';
@endphp
@section('title', $documentType->name)
@section('content')
<div class="container-fluid">
	<h1>{{ $documentType->name }}</h1>
	<div class="mb-3">
		<a wire:navigate href="{{ route('document-types.index') }}" class="btn btn-secondary btn-sm me-1"> <i class="fa fa-arrow-right"></i> {{__('Back to')}} {{__('Document Types')}}</a>
	</div>
	<div class="card">
		<div class="card-body">
			<ul class="list-group list-group-flush">
				<li class="list-group-item">
					<strong>{{ __("Id") }}:</strong> {{ $documentType->id }}
				</li>
				<li class="list-group-item">
					<strong>{{ __("Name") }}:</strong> {{ $documentType->name }}
				</li>
                <li class="list-group-item">
                    <strong>{{ __("Is Required") }}:</strong>
                    @if($documentType->is_required)
                        <span class="badge bg-success text-white">{{ __('Active') }}</span>
                    @else
                        <span class="badge bg-danger text-white">{{ __('Inactive') }}</span>
                    @endif
                </li>
				<li class="list-group-item">
					<strong>{{ __("Created At") }}:</strong> {{ $documentType->created_at? $documentType->created_at->diffForHumans() : '-' }}
				</li>
				<li class="list-group-item">
					<strong>{{ __("Updated At") }}:</strong> {{ $documentType->updated_at? $documentType->updated_at->diffForHumans() : '-' }}
				</li>
			</ul>
		</div>
            <div class="card-footer">
                <a wire:navigate href='{{ route('document-types.edit', $documentType) }}' class="btn btn-warning btn-sm me-1">{{ __('Change Status') }} <i class="fa fa-edit"></i></a>
            </div>
	</div>
</div>
@endsection
