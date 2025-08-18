@extends('layouts.app')
@php
    $currentPage = 'otp-limits';
@endphp
@section('title', $otpLimit->name)
@section('content')
    <div class="container-fluid">
        <h1>{{ $otpLimit->type->label() }}</h1>
        <div class="mb-3">
            <a href="{{ route('otp-limits.index') }}" class="btn btn-secondary btn-sm me-1"> <i class="fa fa-arrow-right"></i>
                {{ __('Back to') }} {{ __('Otp Limits') }}</a>
            {{-- <a href='{{ route('otp-limits.edit', $otpLimit) }}' class="btn btn-warning btn-sm me-1">{{ __('Edit') }} <i class="fa fa-edit"></i></a> --}}
        </div>
        <div class="card">
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <strong>{{ __('Id') }}:</strong> {{ $otpLimit->id }}
                    </li>
                    <li class="list-group-item">
                        <strong>{{ __('Type') }}:</strong> {!! $otpLimit->type->badge() !!}
                    </li>
                    <li class="list-group-item">
                        <strong>{{ __('Otp Limit') }}:</strong> {{ $otpLimit->otp_limit }}
                    </li>
                    <li class="list-group-item">
                        <strong>{{ __('Created At') }}:</strong> {{ $otpLimit->created_at?->diffForHumans() ?? '-' }}
                    </li>
                    <li class="list-group-item">
                        <strong>{{ __('Updated At') }}:</strong> {{ $otpLimit->updated_at?->diffForHumans() ?? '-' }}
                    </li>
                </ul>
            </div>
            <div>
                <div class="card-footer">
                    <a href='{{ route('otp-limits.edit', $otpLimit) }}'
                        class="btn btn-warning btn-sm me-1">{{ __('Edit') }} <i class="fa fa-edit"></i></a>
                    @if ($otpLimit->type->value === 'driver')
                        <form action="{{ route('otp-limits.reset-drivers', $otpLimit) }}" method="POST"
                            style="display:inline;"
                            onsubmit="return confirm('{{ __('Are you sure you want to apply this OTP limit number to all drivers?') }}');">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm me-1">
                                <i class="fa fa-car me-1"></i> {{ __('Apply to all Drivers') }}
                            </button>
                        </form>
                    @else
                        <form action="{{ route('otp-limits.reset-users', $otpLimit) }}" method="POST"
                            style="display:inline;"
                            onsubmit="return confirm('{{ __('Are you sure you want to apply this OTP limit number to all users?') }}');">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-sm me-1">
                                <i class="fa fa-users me-1"></i> {{ __('Apply to all Users') }}
                            </button>
                        </form>
                    @endif


                </div>
            </div>
        </div>
    </div>
@endsection
