@extends('layouts.app')
@php
    $currentPage = 'users';
@endphp
@section('title', __('Users'))
@section('content')
    <div class="container-fluid">
        <h1 class="mb-3">{{ __('Users') }}</h1>

        <div class="mb-3 d-flex justify-content-between align-items-center">
        <div class="d-flex gap-2">
            <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
                {{ __('Create User') }} <i class="fa fa-plus"></i>
            </a>
            <a href="{{ route('users.export', request()->query()) }}" class="btn btn-success btn-sm">
                <i class="fa fa-file-excel me-1"></i>{{ __('Export to Excel') }}
            </a>
            <a href="{{ route('users.export-phones', request()->query()) }}" class="btn btn-outline-success btn-sm">
                <i class="fa fa-phone me-1"></i>{{ __('Export Phone Numbers') }}
            </a>
            <a href="{{ route('signup-gifts.index') }}" class="btn btn-outline-warning btn-sm">
                <i class="fa fa-gift me-1"></i>{{ __('Signup Gift') }}
            </a>
        </div>

        <div class="search-wrapper">
            <form action="{{ route(Route::currentRouteName(),[],false) }}" method="GET" class="d-inline-block">
                <div class="input-group">
					@if (request('keyword'))
						<div class="input-group-append">
							<a class="btn btn-secondary" href="{{ route(Route::currentRouteName(),[],false) }}">
								<i class="fa fa-times"></i>
							</a>
						</div>
					@endif
                    <input type="text" name="keyword" class="form-control" autocomplete="off" placeholder="{{ __('Keyword') }}..." value="{{ request('keyword') }}">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

        <div class='main-card mb-3 card'>
            <div class='card-body'>
                <table class="mb-0 table table-hover">
                    <tr>
                        <th>
                            <a
                                href="{{ route('users.index', ['sort' => 'id', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
                                {{ __('Id') }}
                                @if ($sortField === 'id')
                                    <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a
                                href="{{ route('users.index', ['sort' => 'name', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
                                {{ __('Name') }}
                                @if ($sortField === 'name')
                                    <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a
                                href="{{ route('users.index', ['sort' => 'email', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
                                {{ __('Email') }}
                                @if ($sortField === 'email')
                                    <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a
                                href="{{ route('users.index', ['sort' => 'phone', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
                                {{ __('Phone') }}
                                @if ($sortField === 'phone')
                                    <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                                @endif
                            </a>
                        </th>
                        {{-- <th>
						<a href="{{ route('users.index', ['sort' => 'image', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Image") }}
							@if ($sortField === 'image')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th> --}}
                        <th>
                            <a
                                href="{{ route('users.index', ['sort' => 'wallet', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
                                {{ __('Wallet') }}
                                @if ($sortField === 'wallet')
                                    <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                                @endif
                            </a>
                        </th>
                        {{-- <th>
						<a href="{{ route('users.index', ['sort' => 'role', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Role") }}
							@if ($sortField === 'role')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th> --}}
                        <th>
                            <a
                                href="{{ route('users.index', ['sort' => 'activity', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
                                {{ __('Activity') }}
                                @if ($sortField === 'activity')
                                    <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a
                                href="{{ route('users.index', ['sort' => 'zone_id', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
                                {{ __('Zone') }}
                                @if ($sortField === 'zone_id')
                                    <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a
                                href="{{ route('users.index', ['sort' => 'created_at', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
                                {{ __('Created At') }}
                                @if ($sortField === 'created_at')
                                    <i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>
                                @endif
                            </a>
                        </th>
                        <th class="text-center">{{ __('Actions') }}</th>
                    </tr>
                    @foreach ($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->name??'-' }}</td>
                            <td>{{ $user->email??'-' }}</td>
                            <td>{{ $user->phone??'-' }}</td>
                            {{-- <td>{{ $user->image }}</td> --}}
                            <td>{{ $user->wallet??'-' }}</td>
                            <td>{!! $user->activity->badge() !!}</td>
                            {{-- <td>{{ $user->role }}</td> --}}
                            <td>{{ $user->zone ? $user->zone->name : '-' }}</td>
                            <td>{{ $user->created_at ? $user->created_at->diffForHumans() : '-' }}</td>
                            <td class="text-center">
                                <a href='{{ route('users.show', $user) }}'
                                    class="btn btn-subtle-primary btn-sm me-1">{{ __('Details') }} <i
                                        class="fa fa-eye"></i></a>
                                <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline"
                                    onsubmit="return confirm('{{ __('Are you sure you want to delete this user?') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-subtle-danger btn-sm">
                                        {{ __('Delete') }} <i class="fa fa-trash"></i>
                                    </button>
                                </form>

                                {{-- <a href='{{ route('users.edit', $user) }}'
                                    class="btn btn-subtle-warning btn-sm me-1">{{ __('Edit') }} <i
                                        class="fa fa-edit"></i></a> --}}
                                {{-- <form method='POST' action='{{ route('users.destroy', $user) }}' onsubmit='return confirm("Are you sure you want to delete this item?")'>
							<input type='hidden' name='_method' value='DELETE'>
							<button type='submit' class="btn btn-square btn-danger">{{ __('Delete') }}</button>
						</form> --}}
                            </td>
                        </tr>
                    @endforeach
                </table>
                {{ $users->links('pagination::custom') }}
            </div>
        </div>
    </div>
@endsection
