@extends('layouts.app')
@php
    $currentPage = 'settings';
@endphp
@section('title', __('App Settings'))
@section('content')
    <div class="container-fluid">
        <h1 class="mb-3">{{ __('App Settings') }}</h1>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class='main-card mb-3 card'>
            <div class='card-body'>
                <form method="POST" action="{{ route('settings.update') }}">
                    @csrf
                    @method('PUT')

                    @foreach($settings as $setting)
                        <div class="mb-3">
                            <label for="setting_{{ $setting->key }}" class="form-label">
                                {{ ucwords(str_replace('_', ' ', $setting->key)) }}
                            </label>
                            
                            @if($setting->description)
                                <small class="form-text text-muted d-block">{{ $setting->description }}</small>
                            @endif

                            @if($setting->type === 'boolean')
                                <div class="form-check form-switch">
                                    <!-- Hidden input to ensure unchecked checkboxes send a value -->
                                    <input type="hidden" name="settings[{{ $setting->key }}]" value="0">
                                    <input 
                                        class="form-check-input" 
                                        type="checkbox" 
                                        id="setting_{{ $setting->key }}"
                                        name="settings[{{ $setting->key }}]"
                                        value="1"
                                        {{ $setting->cast_value ? 'checked' : '' }}
                                    >
                                    <label class="form-check-label" for="setting_{{ $setting->key }}">
                                        {{ $setting->cast_value ? 'Enabled' : 'Disabled' }}
                                    </label>
                                </div>
                            @elseif($setting->type === 'integer')
                                <input 
                                    type="number" 
                                    class="form-control" 
                                    id="setting_{{ $setting->key }}"
                                    name="settings[{{ $setting->key }}]"
                                    value="{{ $setting->value }}"
                                >
                            @elseif($setting->type === 'json')
                                <textarea 
                                    class="form-control" 
                                    id="setting_{{ $setting->key }}"
                                    name="settings[{{ $setting->key }}]"
                                    rows="4"
                                >{{ is_array($setting->value) ? json_encode($setting->value, JSON_PRETTY_PRINT) : $setting->value }}</textarea>
                            @else
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    id="setting_{{ $setting->key }}"
                                    name="settings[{{ $setting->key }}]"
                                    value="{{ $setting->value }}"
                                >
                            @endif
                        </div>
                    @endforeach

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            {{ __('Update Settings') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Toggle switch labels
        document.querySelectorAll('.form-check-input[type="checkbox"]').forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                const label = this.nextElementSibling;
                label.textContent = this.checked ? 'Enabled' : 'Disabled';
            });
        });
    </script>
@endsection