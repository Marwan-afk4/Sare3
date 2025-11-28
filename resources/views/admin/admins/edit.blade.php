@extends('layouts.app')
@section('title', __('Edit Admin'))
@section('content')
    <div class="container-fluid">
        <h1 class="mb-3">{{ __('Edit Admin') }}</h1>

        <div class='main-card mb-3 card'>
            <div class='card-body'>
                <form action="{{ route('admins.update', $admin) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="name" class="form-label">{{ __('Name') }}</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ $admin->name }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">{{ __('Email') }}</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ $admin->email }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">{{ __('Phone') }}</label>
                        <input type="text" class="form-control" id="phone" name="phone" value="{{ $admin->phone }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">{{ __('Password (Leave blank to keep current)') }}</label>
                        <input type="password" class="form-control" id="password" name="password">
                    </div>

                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">{{ __('Confirm Password') }}</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                    </div>

                    <div class="mb-3">
                        <label for="role_id" class="form-label">{{ __('Role') }}</label>
                        <select class="form-control" id="role_id" name="role_id" required>
                            <option value="">{{ __('Select Role') }}</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ $admin->hasRole($role->name) ? 'selected' : '' }}>{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
                    <a href="{{ route('admins.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                </form>
            </div>
        </div>
    </div>
@endsection
