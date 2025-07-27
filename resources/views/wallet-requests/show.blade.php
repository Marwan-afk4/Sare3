@extends('layouts.app')
@php
    $currentPage = 'wallet-requests';
@endphp
@section('title', $walletRequest->name)
@section('content')
    <div class="container-fluid">
        <h1>{{ $walletRequest->id }}</h1>
        <div class="mb-3">
            <a href="{{ route('wallet-requests.index') }}" class="btn btn-secondary btn-sm me-1"> <i
                    class="fa fa-arrow-right"></i> {{ __('Back to') }} {{ __('Wallet Requests') }}</a>
            {{-- <a href='{{ route('wallet-requests.edit', $walletRequest) }}' class="btn btn-warning btn-sm me-1">{{ __('Edit') }} <i class="fa fa-edit"></i></a> --}}
        </div>
        <div class="card">
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <strong>{{ __('Id') }}:</strong> {{ $walletRequest->id }}
                    </li>
                    <li class="list-group-item">
                        <strong>{{ __('Driver') }}:</strong> @if($walletRequest->driver)
                            <a href="{{ route('drivers.show', $walletRequest->driver) }}">{{ $walletRequest->driver?->name }}</a>
                        @endif
                    </li>
                    <li class="list-group-item">
                        <strong>{{ __('Amount') }}:</strong> {{ $walletRequest->amount }}
                    </li>
                    <li class="list-group-item">
                        <strong>{{ __('Type') }}:</strong> <span class="badge badge-phoenix fs-10"
                            style="background-color: #{{ $walletRequest->type->color() }}; color: #{{ $walletRequest->type->textColor() }};">
                            <span class="badge-label m-1">{{ $walletRequest->type->label() ?? '-' }}</span>
                        </span>
                    </li>
                    <li class="list-group-item">
                        <strong>{{ __('Status') }}:</strong> <span class="badge badge-phoenix fs-10"
                            style="background-color: #{{ $walletRequest->status->color() }}; color: #{{ $walletRequest->status->textColor() }};">
                            <span class="badge-label m-1">{{ $walletRequest->status->label() ?? '-' }}</span>
                        </span>
                    </li>
                    <li class="list-group-item">
                        <strong>{{ __('Note') }}:</strong> {{ $walletRequest->note ?? '-' }}
                    </li>
                    <li class="list-group-item">
                        <strong>{{ __('Created At') }}:</strong> {{ $walletRequest->created_at->diffForHumans() ?? '-' }}
                    </li>
                    <li class="list-group-item">
                        <strong>{{ __('Updated At') }}:</strong> {{ $walletRequest->updated_at->diffForHumans() ?? '-' }}
                    </li>
                </ul>
            </div>
        </div>
        <div class="mt-3">
            {{-- <form method='POST' action='{{ route('wallet-requests.destroy', $walletRequest) }}' onsubmit='return confirm("Are you sure you want to delete this item?")'>
			<input type='hidden' name='_method' value='DELETE'>
			<button type='submit' class="btn btn-square btn-danger">{{ __('Delete') }}</button>
		</form> --}}
        </div>
    </div>
@endsection
