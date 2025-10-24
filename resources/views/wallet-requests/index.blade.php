@extends('layouts.app')
@php
    $currentPage = 'wallet-requests';
@endphp
@section('title', __('Wallet Requests'))
@section('content')
    <div class="container-fluid">
        <h1 class="mb-3">{{ __('Wallet Requests') }}</h1>
        {{-- <div class="mb-3">
		<a href="{{ route('wallet-requests.create') }}" class="btn btn-primary btn-sm me-1">{{__('Create Wallet Request')}} <i class="fa fa-plus"></i></a>
	</div> --}}
        <div class='main-card mb-3 card'>
            <div class='card-body'>
                <table class="mb-0 table table-hover">
                    <tr>
                        <th>
                            <a
                                href="{{ route('wallet-requests.index', ['sort' => 'id', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
                                {{ __('Id') }}
                                @if ($sortField === 'id')
                                    <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a
                                href="{{ route('wallet-requests.index', ['sort' => 'driver_id', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
                                {{ __('Driver') }}
                                @if ($sortField === 'driver_id')
                                    <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a
                                href="{{ route('wallet-requests.index', ['sort' => 'driver_wallet', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
                                {{ __('Driver Wallet') }}
                                @if ($sortField === 'driver_wallet')
                                    <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a
                                href="{{ route('wallet-requests.index', ['sort' => 'amount', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
                                {{ __('Amount') }}
                                @if ($sortField === 'amount')
                                    <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a
                                href="{{ route('wallet-requests.index', ['sort' => 'type', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
                                {{ __('Type') }}
                                @if ($sortField === 'type')
                                    <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a
                                href="{{ route('wallet-requests.index', ['sort' => 'status', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
                                {{ __('Status') }}
                                @if ($sortField === 'status')
                                    <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a
                                href="{{ route('wallet-requests.index', ['sort' => 'note', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
                                {{ __('Note') }}
                                @if ($sortField === 'note')
                                    <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a
                                href="{{ route('wallet-requests.index', ['sort' => 'created_at', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
                                {{ __('Created At') }}
                                @if ($sortField === 'created_at')
                                    <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                                @endif
                            </a>
                        </th>
                        <th class="text-center">{{ __('Actions') }}</th>
                    </tr>
                    @foreach ($walletRequests as $walletRequest)
                        <tr>
                            <td>{{ $walletRequest->id }}</td>
                            <td>
                                @if ($walletRequest->driver)
                                    <a
                                        href="{{ route('drivers.show', $walletRequest->driver) }}">{{ $walletRequest->driver?->name }}</a>
                                @endif
                            </td>
                            <td>
                                    {{ $walletRequest->driver->wallet }}
                            </td>
                            <td>{{ $walletRequest->amount }}</td>
                            <td>{!! $walletRequest->type->badge() !!}</td>
                            <td>{!! $walletRequest->status->badge() !!}</td>
                            <td>{{ $walletRequest->note ?? '-' }}</td>
                            <td>{{ $walletRequest->created_at->diffForHumans() ?? '-' }}</td>
                            <td class="text-center">
                                <a href='{{ route('wallet-requests.show', $walletRequest) }}'
                                    class="btn btn-subtle-primary btn-sm me-1">{{ __('Details') }} <i
                                        class="fa fa-eye"></i></a>
                                <a href='{{ route('wallet-requests.edit', $walletRequest) }}'
                                    class="btn btn-subtle-warning btn-sm me-1">
                                    <i class="fa fa-edit"></i> {{ __('Action') }}
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </table>
                {{ $walletRequests->links('pagination::custom') }}
            </div>
        </div>
    </div>
@endsection
