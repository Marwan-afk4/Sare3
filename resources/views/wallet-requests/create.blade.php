@extends('layouts.app')
@php
	$currentPage = 'wallet-requests';
@endphp
@section('title', __('Create Wallet Request'))
@section('content')
<div class="container">
	<h1>{{ __('Create Wallet Request') }}</h1>
	<div class="mb-3">
		<a href="{{ route('wallet-requests.index') }}" class="btn btn-secondary btn-sm me-1"> <i class="fa fa-arrow-right"></i> {{__('Back to')}} {{__('Wallet Requests')}}</a>
	</div>
	<div class="main-card mb-3 card">
		<div class="card-body">
			<form method='POST' action='{{ route('wallet-requests.store') }}' class="needs-validation" novalidate>
				@csrf
				<x-form-select 
					name="driver_id"
					type="select"
					label="{{__('Driver')}}"
					:selected="$wallet_request->driver_id ?? ''"
					:options="$drivers"
				/>
				<x-form-input 
					name="amount"
					type="text"
					label="{{__('Amount')}}"
					required
				/>
				<x-form-input 
					name="type"
					type="text"
					label="{{__('Type')}}"
					required
				/>
				<x-form-input 
					name="status"
					type="text"
					label="{{__('Status')}}"
					required
				/>
				<x-form-input 
					name="note"
					type="text"
					label="{{__('Note')}}"
				/>
				<button type='submit' class="btn btn-primary btn-sm me-1">{{ __('Add') }}</button>
			</form>
		</div>
	</div>
</div>@endsection