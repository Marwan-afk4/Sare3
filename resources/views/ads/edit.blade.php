@extends('layouts.app')
@php
    $currentPage = 'ads';
@endphp
@section('title', __('Edit Ad'))
@section('content')
    <div class="container">
        <h1>{{ __('Edit Ad') }}</h1>
        <div class="mb-3">
            <a href="{{ route('ads.index') }}" class="btn btn-secondary btn-sm me-1"> <i class="fa fa-arrow-right"></i>
                {{ __('Back to') }} {{ __('Ads') }}</a>
            <a href='{{ route('ads.show', $ad) }}' class="btn btn-primary btn-sm me-1">{{ __('Details') }} <i
                    class="fa fa-eye"></i></a>
        </div>
        <div class="main-card mb-3 card">
            <div class="card-body">
                <form method='POST' action='{{ route('ads.update', $ad->id) }}' class="needs-validation"
                    enctype="multipart/form-data" novalidate>
                    @csrf
                    @method('PUT')
                    <x-form-input name="title" type="text" label="{{ __('Title') }}" :value="$ad->title ?? ''" />
                    <x-form-textarea name="description" type="text" label="{{ __('Description') }}" :value="$ad->description ?? ''" />

                    @if ($ad->image)
                        <div class="mb-3">
                            <label class="form-label">{{ __('Current Image') }}</label>
                            <div>
                                <a href="{{ $ad->image_link }}" target="_blank">
                                    <img src="{{ $ad->image_link }}" style="max-width: 200px; height: auto;"
                                        class="img-thumbnail">
                                </a>
                            </div>
                        </div>
                    @endif

                    <x-form-input name="image" type="file"
                        label="{{ __('Image') }} ({{ __('Leave empty to keep current image') }})" :attributes="['accept' => 'image/*']" />
                    <button type='submit' class="btn btn-warning btn-sm me-1">{{ __('Save') }}</button>
                </form>
            </div>
        </div>
    </div>
@endsection
