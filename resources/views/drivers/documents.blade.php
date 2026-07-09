@extends('layouts.app')
@php
    $currentPage = 'drivers';
@endphp
@section('title', __('Driver Documents for ') . $driver->name)
@section('content')
<div class="container-fluid">
    <h1>{{ __('Documents for') }} {{ $driver->name }}</h1>

    <div class="mb-3 d-flex flex-wrap gap-2 align-items-center">
        <a href="{{ route('drivers.show', $driver->id) }}" class="btn btn-secondary btn-sm">
            <i class="fa fa-arrow-right"></i> {{ __('Back to Driver') }}
        </a>
        <a href="{{ route('driver-documents.create', ['driver_id' => $driver->id, 'redirect_driver_id' => $driver->id]) }}" class="btn btn-primary btn-sm">
            <i class="fa fa-plus"></i> {{ __('Add Document') }}
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('Close') }}"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('Close') }}"></button>
        </div>
    @endif

    <div class="row">
        @if ($driver->image)
            <div class="col-md-3 mb-4">
                <div class="card h-100 border border-primary">
                    <img src="{{ $driver->image_link }}" class="card-img-top" alt="{{ __('Personal Photo') }}" style="height: 200px; object-fit: cover;">

                    <div class="card-body">
                        <h5 class="card-title">{{ __('Personal Photo') }}</h5>
                        <p class="card-text"><small class="text-muted">{{ __('Uploaded at registration') }}</small></p>

                        <div class="d-flex gap-1 flex-wrap">
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#selfieModal">
                                {{ __('View Full Image') }} <i class="fa fa-image"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Selfie Modal -->
            <div class="modal fade" id="selfieModal" tabindex="-1" aria-labelledby="selfieModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="selfieModalLabel">{{ __('Personal Photo') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                        </div>
                        <div class="modal-body text-center">
                            <img src="{{ $driver->image_link }}" class="img-fluid" alt="{{ __('Personal Photo') }}">
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @foreach ($documents as $document)
            @php
                $imageUrl = $document->image_link ?? 'https://archive.org/download/placeholder-image/placeholder-image.jpg';
            @endphp

            <div class="col-md-3 mb-4">
                <div class="card h-100">
                    <img src="{{ $imageUrl }}" class="card-img-top" alt="{{ $document->documentType->name }}" style="height: 200px; object-fit: cover;">

                    <div class="card-body">
                        <h5 class="card-title">{{ $document->documentType->name }}</h5>
                        <p class="card-text"><small class="text-muted">{{ $document->created_at->diffForHumans() }}</small></p>

                        <div class="d-flex gap-1 flex-wrap">
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#imageModal{{ $document->id }}">
                                {{ __('View Full Image') }} <i class="fa fa-image"></i>
                            </button>
                            <a href="{{ route('driver-documents.edit', $document) }}" class="btn btn-warning btn-sm">
                                {{ __('Edit') }} <i class="fa fa-edit"></i>
                            </a>
                            <form method="POST" action="{{ route('driver-documents.destroy', $document) }}" class="d-inline" onsubmit="return confirm('{{ __('Are you sure you want to delete this document?') }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">{{ __('Delete') }} <i class="fa fa-trash"></i></button>
                            </form>
                        </div>
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
        @endforeach

        @if (!$driver->image && $documents->isEmpty())
            <div class="col-12">
                <div class="alert alert-info">{{ __('No documents found for this driver.') }}</div>
            </div>
        @endif
    </div>
</div>
@endsection
