@extends('layouts.app')
@php
    $currentPage = 'paymenent-methods';
@endphp
@section('title', $paymenentMethod->name)
@section('content')
    <div class="container-fluid">
        <h1>{{ $paymenentMethod->name }}</h1>
        <div class="mb-3">
            <a href="{{ route('paymenent-methods.index') }}" class="btn btn-secondary btn-sm me-1"> <i
                    class="fa fa-arrow-right"></i> {{ __('Back to') }} {{ __('Paymenent Methods') }}</a>
            {{-- <a href='{{ route('paymenent-methods.edit', $paymenentMethod) }}' class="btn btn-warning btn-sm me-1">{{ __('Edit') }} <i class="fa fa-edit"></i></a> --}}
        </div>
        <div class="card">
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <strong>{{ __('Id') }}:</strong> {{ $paymenentMethod->id }}
                    </li>
                    <li class="list-group-item">
                        <strong>{{ __('Name') }}:</strong> {{ $paymenentMethod->name }}
                    </li>
                    <li class="list-group-item">
                        <strong>{{ __('Status') }}:</strong> <span class="badge badge-phoenix fs-10"
                            style="background-color: #{{ $paymenentMethod->status->color() }}; color: #{{ $paymenentMethod->status->textColor() }};">
                            <span class="badge-label m-1">{{ $paymenentMethod->status->label() ?? '-' }}</span>
                    </li>
                    <li class="list-group-item">
                        <strong>{{ __('Created At') }}:</strong> {{ $paymenentMethod->created_at->diffForHumans() ?? '-' }}
                    </li>
                    <li class="list-group-item">
                        <strong>{{ __('Updated At') }}:</strong> {{ $paymenentMethod->updated_at->diffForHumans() ?? '-' }}
                    </li>
                </ul>
            </div>
        </div>
        <div class="mt-3">
            {{-- <form method='POST' action='{{ route('paymenent-methods.destroy', $paymenentMethod) }}' onsubmit='return confirm("Are you sure you want to delete this item?")'>
			<input type='hidden' name='_method' value='DELETE'>
			<button type='submit' class="btn btn-square btn-danger">{{ __('Delete') }}</button>
		</form> --}}
        </div>
    </div>
@endsection
