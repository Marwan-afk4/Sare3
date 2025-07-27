@extends('layouts.app')
@php
	$currentPage = 'paymenent-methods';
@endphp
@section('title', __('Edit Paymenent Method'))
@section('content')
<div class="container">
	<h1>{{ __('Edit Paymenent Method') }}</h1>
	<div class="mb-3">
		<a href="{{ route('paymenent-methods.index') }}" class="btn btn-secondary btn-sm me-1"> <i class="fa fa-arrow-right"></i> {{__('Back to')}} {{__('Paymenent Methods')}}</a>
		<a href='{{ route('paymenent-methods.show', $paymenentMethod) }}' class="btn btn-primary btn-sm me-1">{{ __("Details") }} <i class="fa fa-eye"></i></a>
	</div>
	<div class="main-card mb-3 card">
		<div class="card-body">
			<form method='POST' action='{{ route('paymenent-methods.update', $paymenentMethod->id) }}' class="needs-validation" novalidate>
				@csrf
				@method('PUT')
				<x-form-select
					name="status"
					type="select"
					label="{{__('Status')}}"
					:selected="$paymenentMethod->status->value ??''"
                    :options="$statuses"
					required
				/>
				<button type='submit' class="btn btn-warning btn-sm me-1">{{ __('Save') }}</button>
			</form>
		</div>
	</div>
</div>
@endsection
