@extends('layouts.app')
@section('title', __('Create Role'))
@section('content')
    <div class="container-fluid">
        <h1 class="mb-3">{{ __('Create Role') }}</h1>

        <div class='main-card mb-3 card'>
            <div class='card-body'>
                <form action="{{ route('roles.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label">{{ __('Role Name') }}</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Permissions') }}</label>
                        <div class="row">
                            @foreach($permissions as $permission)
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->name }}" id="perm_{{ $permission->id }}">
                                        <label class="form-check-label" for="perm_{{ $permission->id }}">
                                            {{ $permission->name }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">{{ __('Create') }}</button>
                    <a href="{{ route('roles.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                </form>
            </div>
        </div>
    </div>
@endsection
