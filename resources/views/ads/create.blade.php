@extends('layouts.app')
@php
	$currentPage = 'ads';
@endphp
@section('title', __('Create Ad'))
@section('content')
<div class="container">
	<h1>{{ __('Create Ad') }}</h1>
	<div class="mb-3">
		<a href="{{ route('ads.index') }}" class="btn btn-secondary btn-sm me-1"> <i class="fa fa-arrow-right"></i> {{__('Back to')}} {{__('Ads')}}</a>
	</div>
	<div class="main-card mb-3 card">
		<div class="card-body">
			<form method='POST' action='{{ route('ads.store') }}' class="needs-validation" enctype="multipart/form-data" novalidate>
				@csrf
				<x-form-input 
					name="title"
					type="text"
					label="{{__('Title')}}"
					required
				/>
				<x-form-textarea 
					name="description"
					type="text"
					label="{{__('Description')}}"
				/>
				<x-form-input
					name="image"
					type="file"
					label="{{__('Image')}}"
					required
					:attributes="['accept' => 'image/*']"
				/>
				<button type='submit' class="btn btn-primary btn-sm me-1">{{ __('Add') }}</button>
			</form>
		</div>
	</div>
</div>@endsection