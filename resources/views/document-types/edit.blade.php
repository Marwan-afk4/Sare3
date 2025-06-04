@extends('layouts.app')
@php
	$currentPage = 'document-types';
@endphp
@section('title', __('Edit Document Type'))
@section('content')
<div class="container">
	<h1>{{ __('Edit Document Type') }}</h1>
	<div class="mb-3">
		<a href="{{ route('document-types.index') }}" class="btn btn-secondary btn-sm me-1"> <i class="fa fa-arrow-right"></i> {{__('Back to')}} {{__('Document Types')}}</a>
		<a href='{{ route('document-types.show', $documentType) }}' class="btn btn-primary btn-sm me-1">{{ __("Details") }} <i class="fa fa-eye"></i></a>
	</div>
	<div class="main-card mb-3 card">
		<div class="card-body">
			<form method='POST' action='{{ route('document-types.update', $documentType->id) }}' class="needs-validation" novalidate>
				@csrf
				@method('PUT')
				<x-form-input
					name="name"
					type="text"
					label="{{__('Name')}}"
					:value="$documentType->name ?? ''"
					disabled
				/>
				<x-form-select
					name="is_required"
					type="select"
					label="{{__('Is Required')}}"
                    :options="[
                        1 => __('Active'),
                        0 => __('Inactive')
                    ]"
                    :selected="$documentType->is_required ?? 0"
					required
				/>
				<button type='submit' class="btn btn-warning btn-sm me-1">{{ __('Save') }}</button>
			</form>
		</div>
	</div>
</div>
@endsection
