@extends('layouts.app')
@php
	$currentPage = 'document-types';
@endphp
@section('title', __('Create Document Type'))
@section('content')
<div class="container">
	<h1>{{ __('Create Document Type') }}</h1>
	<div class="mb-3">
		<a href="{{ route('document-types.index') }}" class="btn btn-secondary btn-sm me-1"> <i class="fa fa-arrow-right"></i> {{__('Back to')}} {{__('Document Types')}}</a>
	</div>
	<div class="main-card mb-3 card">
		<div class="card-body">
			<form method='POST' action='{{ route('document-types.store') }}' class="needs-validation" novalidate>
				@csrf
				<x-form-input
					name="name"
					type="text"
					label="{{__('Name')}}"
					required
				/>
				<x-form-select
					name="is_required"
					type="select"
					label="{{__('Is Required')}}"
					required
                    :options="[
                        1 => __('Active'),
                        0 => __('Inactive')
                    ]"
				/>
				<button type='submit' class="btn btn-primary btn-sm me-1">{{ __('Add') }}</button>
			</form>
		</div>
	</div>
</div>@endsection
