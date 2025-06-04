@extends('layouts.app')
@php
    $currentPage = 'drivers';
@endphp
@section('title', __('Driver Documents for ') . $driver->name)
@section('content')
<div class="container-fluid">
    <h1>{{ __('Documents for') }} {{ $driver->name }}</h1>

    <a wire:navigate href="{{ route('drivers.show', $driver->id) }}" class="btn btn-secondary btn-sm mb-3">
        <i class="fa fa-arrow-right"></i> {{ __('Back to Driver') }}
    </a>

    <div class="row">
        @forelse ($documents as $document)
            @php
                $imagePath = $document->image_path;
                $imageUrl = \Illuminate\Support\Facades\Storage::disk('public')->exists($imagePath)
                    ? asset('storage/' . $imagePath)
                    : 'https://archive.org/download/placeholder-image/placeholder-image.jpg';
            @endphp

            <div class="col-md-3 mb-4">
                <div class="card h-100">
                    <img src="{{ $imageUrl }}" class="card-img-top" alt="{{ $document->documentType->name }}" style="height: 200px; object-fit: cover;">

                    <div class="card-body">
                        <h5 class="card-title">{{ $document->documentType->name }}</h5>
                        <p class="card-text"><small class="text-muted">{{ $document->created_at->diffForHumans() }}</small></p>

                        <!-- View Full Image Button -->
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#imageModal{{ $document->id }}">
                            {{ __('View Full Image') }} <i class="fa fa-image"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Bootstrap Modal -->
            <div class="modal fade" id="imageModal{{ $document->id }}" tabindex="-1" aria-labelledby="imageModalLabel{{ $document->id }}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="imageModalLabel{{ $document->id }}">{{ $document->documentType->name }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                        </div>
                        <div class="modal-body text-center">
                            <img src="{{ $imageUrl }}" class="img-fluid" alt="{{ $document->documentType->name }}">
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info">{{ __('No documents found for this driver.') }}</div>
            </div>
        @endforelse
    </div>
</div>
@endsection
