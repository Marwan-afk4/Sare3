@extends('layouts.app')
@php
    $currentPage = 'roles';
@endphp
@section('title', __('Roles & Permissions'))
@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="row align-items-center mb-4">
            <div class="col">
                <h2 class="mb-1">{{ __('Roles & Permissions') }}</h2>
                <p class="text-muted mb-0">{{ __('Manage user roles and their permissions') }}</p>
            </div>
            <div class="col-auto">
                <a href="{{ route('roles.create') }}" class="btn btn-primary">
                    <i class="fa fa-plus me-2"></i>{{ __('Create New Role') }}
                </a>
            </div>
        </div>

        <!-- Roles Grid -->
        <div class="row g-3">
            @forelse ($roles as $role)
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="card h-100 role-card border">
                        <div class="card-body">
                            <!-- Role Header -->
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="role-icon me-2">
                                            <i class="fa fa-shield-alt"></i>
                                        </div>
                                        <h5 class="mb-0">{{ ucfirst($role->name) }}</h5>
                                    </div>
                                    <small class="text-muted">
                                        <i class="fa fa-key me-1"></i>
                                        {{ $role->permissions->count() }} {{ __('permissions') }}
                                    </small>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                        <i class="fa fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('roles.edit', $role) }}">
                                                <i class="fa fa-edit me-2 text-primary"></i>{{ __('Edit') }}
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            @if(! in_array($role->name, ['admin', 'super-admin'], true))
                                                <form action="{{ route('roles.destroy', $role) }}" method="POST" 
                                                    onsubmit="return confirm('{{ __('Are you sure you want to delete this role?') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="fa fa-trash me-2"></i>{{ __('Delete') }}
                                                    </button>
                                                </form>
                                            @else
                                                <button type="button" class="dropdown-item text-muted" disabled title="{{ __('Cannot delete system role') }}">
                                                    <i class="fa fa-lock me-2"></i>{{ __('Locked') }}
                                                </button>
                                            @endif
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Permissions List -->
                            <div class="permissions-list mb-3">
                                @if($role->permissions->count() > 0)
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($role->permissions->take(6) as $permission)
                                            <span class="permission-badge">
                                                {{ ucfirst(str_replace('manage ', '', $permission->name)) }}
                                            </span>
                                        @endforeach
                                        @if($role->permissions->count() > 6)
                                            <span class="permission-badge more-badge">
                                                +{{ $role->permissions->count() - 6 }}
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <p class="text-muted mb-0 small text-center py-2">
                                        {{ __('No permissions assigned') }}
                                    </p>
                                @endif
                            </div>

                            <!-- Users Count -->
                            <div class="users-count">
                                <i class="fa fa-users me-1"></i>
                                <span>{{ $role->users->count() ?? 0 }}</span>
                                <small class="text-muted">{{ __('users assigned') }}</small>
                            </div>
                        </div>

                        <!-- Card Footer -->
                        <div class="card-footer border-top">
                            <a href="{{ route('roles.edit', $role) }}" class="btn btn-sm btn-outline-primary w-100">
                                {{ __('Manage Role') }}
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <!-- Empty State -->
                <div class="col-12">
                    <div class="card border">
                        <div class="card-body text-center py-5">
                            <i class="fa fa-shield-alt fa-3x text-muted mb-3 opacity-50"></i>
                            <h5 class="text-muted mb-2">{{ __('No roles found') }}</h5>
                            <p class="text-muted mb-4">{{ __('Create your first role to get started') }}</p>
                            <a href="{{ route('roles.create') }}" class="btn btn-primary">
                                <i class="fa fa-plus me-2"></i>{{ __('Create Role') }}
                            </a>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($roles->hasPages())
            <div class="mt-4">
                {{ $roles->links('pagination::custom') }}
            </div>
        @endif
    </div>

    <style>
        /* Role Cards */
        .role-card {
            transition: all 0.3s ease;
        }
        .role-card:hover {
            border-color: var(--bs-primary) !important;
            box-shadow: 0 0.25rem 0.75rem rgba(13, 110, 253, 0.15);
            transform: translateY(-2px);
        }

        /* Dark mode support */
        [data-bs-theme="dark"] .role-card:hover {
            box-shadow: 0 0.25rem 0.75rem rgba(13, 110, 253, 0.3);
        }

        /* Role Icon */
        .role-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1rem;
        }

        /* Permission Badges */
        .permission-badge {
            display: inline-block;
            padding: 0.35rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 500;
            background-color: var(--bs-secondary-bg);
            border: 1px solid var(--bs-border-color);
            border-radius: 6px;
            transition: all 0.2s ease;
            color: var(--bs-body-color);
        }
        .permission-badge:hover {
            background-color: var(--bs-tertiary-bg);
            border-color: var(--bs-border-color-translucent);
        }
        .more-badge {
            background-color: var(--bs-primary);
            color: white;
            border-color: var(--bs-primary);
        }
        .more-badge:hover {
            background-color: var(--bs-primary);
            opacity: 0.9;
        }

        /* Permissions List */
        .permissions-list {
            min-height: 80px;
            max-height: 120px;
            overflow-y: auto;
        }
        .permissions-list::-webkit-scrollbar {
            width: 4px;
        }
        .permissions-list::-webkit-scrollbar-track {
            background: var(--bs-secondary-bg);
            border-radius: 10px;
        }
        .permissions-list::-webkit-scrollbar-thumb {
            background: var(--bs-border-color);
            border-radius: 10px;
        }
        .permissions-list::-webkit-scrollbar-thumb:hover {
            background: var(--bs-secondary-color);
        }

        /* Users Count */
        .users-count {
            padding: 0.75rem;
            background-color: var(--bs-secondary-bg);
            border-radius: 6px;
            text-align: center;
            font-size: 0.875rem;
        }
        .users-count i {
            color: var(--bs-secondary-color);
        }
        .users-count span {
            font-weight: 600;
            color: var(--bs-body-color);
            margin: 0 0.25rem;
        }

        /* Dropdown */
        .dropdown-item:hover {
            background-color: var(--bs-secondary-bg);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .role-card {
                margin-bottom: 1rem;
            }
        }
    </style>
@endsection