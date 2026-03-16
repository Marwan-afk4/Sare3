@extends('layouts.app')
@php
    $currentPage = 'admins';
@endphp
@section('title', __('Admins'))
@section('content')
    <div class="container-fluid">
        <h1 class="mb-3">{{ __('Admins') }}</h1>

        <div class="mb-3 d-flex justify-content-between align-items-center">
            <a href="{{ route('admins.create') }}" class="btn btn-primary btn-sm me-1">
                {{ __('Create Admin') }} <i class="fa fa-plus"></i>
            </a>
        </div>

        <div class='main-card mb-3 card'>
            <div class='card-body'>
                <table class="mb-0 table table-hover">
                    <thead>
                        <tr>
                            <th>{{ __('Name') }}</th>
                            <th>{{ __('Email') }}</th>
                            <th>{{ __('Phone') }}</th>
                            <th>{{ __('Role') }}</th>
                            <th>{{ __('Wallet Limit') }}</th>
                            <th class="text-center">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($admins as $admin)
                            <tr>
                                <td>{{ $admin->name }}</td>
                                <td>{{ $admin->email }}</td>
                                <td>{{ $admin->phone }}</td>
                                <td>
                                    @foreach ($admin->roles as $role)
                                        <span class="badge bg-info">{{ $role->name }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    <span
                                        class="badge bg-{{ $admin->wallet_limit > 0 ? 'success' : 'secondary' }} rounded-pill">
                                        ${{ number_format($admin->wallet_limit, 2) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <!-- Manage Wallet Limit Button -->
                                    <button type="button" class="btn btn-info btn-sm me-1" data-bs-toggle="modal"
                                        data-bs-target="#walletLimitModal{{ $admin->id }}">
                                        <i class="fa fa-wallet"></i> {{ __('Limit') }}
                                    </button>

                                    <a href='{{ route('admins.edit', $admin) }}'
                                        class="btn btn-subtle-warning btn-sm me-1">{{ __('Edit') }} <i
                                            class="fa fa-edit"></i></a>
                                    @if($admin->phone !== '01111679168')
                                        <form action="{{ route('admins.destroy', $admin) }}" method="POST" class="d-inline"
                                            onsubmit="return confirm('{{ __('Are you sure you want to delete this admin?') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-subtle-danger btn-sm">
                                                {{ __('Delete') }} <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    @else
                                        <button type="button" class="btn btn-subtle-secondary btn-sm" disabled title="{{ __('Cannot delete main admin') }}">
                                            {{ __('Delete') }} <i class="fa fa-lock"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>

                            <!-- Wallet Limit Modal -->
                            <div class="modal fade" id="walletLimitModal{{ $admin->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">{{ __('Manage Wallet Limit') }} - {{ $admin->name }}
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">{{ __('Current Limit') }}</label>
                                                <input type="text" class="form-control"
                                                    value="${{ number_format($admin->wallet_limit, 2) }}" readonly>
                                            </div>

                                            <!-- Add to Limit Form -->
                                            <form method="POST"
                                                action="{{ route('admins.update-wallet-limit', $admin) }}">
                                                @csrf
                                                <input type="hidden" name="action" value="add">
                                                <label class="form-label">{{ __('Amount to Add') }} <span
                                                        class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text">$</span>
                                                    <input type="number" name="amount" class="form-control" step="0.01"
                                                        min="0.01" placeholder="0.00" required>
                                                    <button type="submit" class="btn btn-success">
                                                        <i class="fa fa-plus"></i> {{ __('Add') }}
                                                    </button>
                                                </div>
                                                <small
                                                    class="text-muted">{{ __('This will be added to the current limit') }}</small>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </tbody>
                </table>
                {{ $admins->links('pagination::custom') }}
            </div>
        </div>
    </div>
@endsection
