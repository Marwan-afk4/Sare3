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
                                    @foreach($admin->roles as $role)
                                        <span class="badge bg-info">{{ $role->name }}</span>
                                    @endforeach
                                </td>
                                <td class="text-center">
                                    <a href='{{ route('admins.edit', $admin) }}'
                                        class="btn btn-subtle-warning btn-sm me-1">{{ __('Edit') }} <i
                                            class="fa fa-edit"></i></a>
                                    <form action="{{ route('admins.destroy', $admin) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('{{ __('Are you sure you want to delete this admin?') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-subtle-danger btn-sm">
                                            {{ __('Delete') }} <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $admins->links('pagination::custom') }}
            </div>
        </div>
    </div>
@endsection
