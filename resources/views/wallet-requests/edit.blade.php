@extends('layouts.app')
@php
	$currentPage = 'wallet-requests';
@endphp
@section('title', __('Edit Wallet Request'))
@section('content')
<div class="container">
	<h1>{{ __('Edit Wallet Request') }}</h1>
	<div class="mb-3">
		<a href="{{ route('wallet-requests.index') }}" class="btn btn-secondary btn-sm me-1"> <i class="fa fa-arrow-right"></i> {{__('Back to')}} {{__('Wallet Requests')}}</a>
		<a href='{{ route('wallet-requests.show', $walletRequest) }}' class="btn btn-primary btn-sm me-1">{{ __("Details") }} <i class="fa fa-eye"></i></a>
	</div>
	<div class="main-card mb-3 card">
		<div class="card-body">
			<form method='POST' action='{{ route('wallet-requests.update', $walletRequest->id) }}' class="needs-validation" novalidate>
				@csrf
				@method('PUT')
				<x-form-select
					name="status"
					type="select"
					label="{{__('Status')}}"
					:options="$statuses"
                    :selected="$walletRequest->status->value ?? ''"
					required
				/>
                <x-form-textarea
                    name="admin_message"
                    type="text"
                    label="{{__('Admin Message to Driver')}}"
                    :value="''"
                />
				<x-form-input
					name="note"
					type="text"
					label="{{__('Note')}}"
					:value="''"
				/>
				<button type='submit' class="btn btn-warning btn-sm me-1">{{ __('Save') }}</button>
			</form>
		</div>
	</div>
</div>
@endsection
