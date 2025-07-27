@extends('layouts.app')
@php
	$currentPage = 'paymenent-methods';
@endphp
@section('title', __('Create Paymenent Method'))
@section('content')
<div class="container">
	<h1>{{ __('Create Paymenent Method') }}</h1>
	<div class="mb-3">
		<a href="{{ route('paymenent-methods.index') }}" class="btn btn-secondary btn-sm me-1"> <i class="fa fa-arrow-right"></i> {{__('Back to')}} {{__('Paymenent Methods')}}</a>
	</div>
	<div class="main-card mb-3 card">
		<div class="card-body">
			<form method='POST' action='{{ route('paymenent-methods.store') }}' class="needs-validation" novalidate>
				@csrf
				<x-form-input 
					name="name"
					type="text"
					label="{{__('Name')}}"
					required
				/>
				<x-form-input 
					name="status"
					type="text"
					label="{{__('Status')}}"
					required
				/>
				<button type='submit' class="btn btn-primary btn-sm me-1">{{ __('Add') }}</button>
			</form>
		</div>
	</div>
</div>@endsection