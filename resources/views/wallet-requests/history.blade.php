@extends('layouts.app')
@php
    $currentPage = 'wallet-requests';
@endphp
@section('title', __('Wallet Transaction History') . ' - ' . $driver->name)
@section('content')
    <div class="container-fluid">
        <div class="mb-3">
            <a href="{{ route('wallet-requests.index') }}" class="btn btn-secondary btn-sm me-1">
                <i class="fa fa-arrow-right"></i> {{ __('Back to') }} {{ __('Driver Wallets Management') }}
            </a>
        </div>

        <!-- Driver Info Card -->
        <div class="card mb-3">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-2 text-center">
                        @if ($driver->image)
                            <img src="{{ $driver->image_link }}" alt="{{ $driver->name }}" class="img-thumbnail"
                                style="max-width: 120px;">
                        @else
                            <div class="bg-secondary text-white rounded d-flex align-items-center justify-content-center"
                                style="width: 120px; height: 120px; font-size: 48px;">
                                <i class="fa fa-user"></i>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-10">
                        <h3 class="mb-2">{{ $driver->name }}</h3>
                        <div class="row">
                            <div class="col-md-4">
                                <p class="mb-1"><strong>{{ __('Phone') }}:</strong> {{ $driver->phone }}</p>
                                <p class="mb-1"><strong>{{ __('Email') }}:</strong> {{ $driver->email ?? '-' }}</p>
                            </div>
                            <div class="col-md-4">
                                <p class="mb-1"><strong>{{ __('Current Balance') }}:</strong></p>
                                <h4>
                                    <span
                                        class="badge bg-{{ $driver->wallet >= 0 ? 'success' : 'danger' }} rounded-pill px-4 py-2">
                                        ${{ number_format($driver->wallet, 2) }}
                                    </span>
                                </h4>
                            </div>
                            <div class="col-md-4">
                                <p class="mb-1"><strong>{{ __('Total Transactions') }}:</strong>
                                    {{ $walletHistory->total() }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Wallet Transaction History Card -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fa fa-wallet"></i> {{ __('Wallet Transaction History') }}
                </h5>
            </div>
            <div class="card-body">
                @if ($walletHistory->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('ID') }}</th>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Note') }}</th>
                                    <th>{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($walletHistory as $transaction)
                                    <tr>
                                        <td>#{{ $transaction->id }}</td>
                                        <td>
                                            <div>{{ $transaction->created_at->format('M d, Y') }}</div>
                                            <small
                                                class="text-muted">{{ $transaction->created_at->format('h:i A') }}</small>
                                        </td>
                                        <td>
                                            {!! $transaction->type->badge() !!}
                                        </td>
                                        <td>
                                            <span
                                                class="fw-bold {{ $transaction->type->value === 'deposit' ? 'text-success' : 'text-danger' }}">
                                                {{ $transaction->type->value === 'deposit' ? '+' : '-' }}${{ number_format($transaction->amount, 2) }}
                                            </span>
                                        </td>
                                        <td>
                                            <small>{{ $transaction->note ?? '-' }}</small>
                                        </td>
                                        <td>
                                            {!! $transaction->status->badge() !!}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-3">
                        {{ $walletHistory->links('pagination::custom') }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fa fa-wallet fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">{{ __('No wallet transactions yet') }}</h5>
                        <p class="text-muted">{{ __('This driver has no wallet transaction history') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
