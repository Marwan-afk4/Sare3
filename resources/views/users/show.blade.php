@extends('layouts.app')
@php
    $currentPage = 'users';
@endphp
@section('title', $user->name)
@section('content')
    <div class="container-fluid">
        <h1>{{ $user->name }}</h1>
        <div class="mb-3">
            <a href="{{ route('users.index') }}" class="btn btn-secondary btn-sm me-1">
                <i class="fa fa-arrow-right"></i> {{ __('Back to') }} {{ __('Users') }}
            </a>
            <a href='{{ route('users.edit', $user) }}' class="btn btn-warning btn-sm me-1">
                {{ __('Edit') }} <i class="fa fa-edit"></i>
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="row">
                    {{-- Profile Image --}}
                    <div class="col-md-2 text-center">
                        @if ($user->image)
                            <img src="{{ asset('storage/' . $user->image) }}" alt="{{ $user->name }}"
                                class="img-thumbnail mb-3" style="max-width: 100%;">
                        @else
                            <img src="https://th.bing.com/th/id/OIP.hGSCbXlcOjL_9mmzerqAbQHaHa?rs=1&pid=ImgDetMain"
                                alt="No Image" class="img-thumbnail mb-3">
                        @endif
                    </div>

                    {{-- Info --}}
                    <div class="col-md-9">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item"><strong>{{ __('Id') }}:</strong> {{ $user->id }}</li>
                            <li class="list-group-item"><strong>{{ __('Name') }}:</strong> {{ $user->name }}</li>
                            <li class="list-group-item"><strong>{{ __('Email') }}:</strong> {{ $user->email }}</li>
                            <li class="list-group-item"><strong>{{ __('Phone') }}:</strong> {{ $user->phone }}</li>
                            <li class="list-group-item"><strong>{{ __('Activity') }}:</strong>
                                <span class="badge badge-phoenix fs-10"
                                    style="background-color: #{{ $user->activity->color() }}; color: #{{ $user->activity->textColor() }};">
                                    <span class="badge-label m-1">{{ $user->activity->label() ?? '-' }}</span>
                                </span>
                            </li>
                            <li class="list-group-item"><strong>{{ __('Wallet') }}:</strong> {{ $user->wallet }}</li>
                            <li class="list-group-item"><strong>{{ __('Role') }}:</strong> {{ $user->role }}</li>
                            <li class="list-group-item"><strong>{{ __('Created At') }}:</strong>
                                {{ $user->created_at?->diffForHumans() }}</li>
                            <li class="list-group-item"><strong>{{ __('Updated At') }}:</strong>
                                {{ $user->updated_at?->diffForHumans() }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-3">
            {{-- Optional Delete Form --}}
            {{--
		<form method='POST' action='{{ route('users.destroy', $user) }}' onsubmit='return confirm("Are you sure?")'>
			@csrf
			@method('DELETE')
			<button type='submit' class="btn btn-danger">{{ __('Delete') }}</button>
		</form>
		--}}
        </div>
    </div>
@endsection
