@extends('layouts.app')
@php
	$currentPage = 'cancellation-reasons';
@endphp
@section('title', $cancellationReason->name)
@section('content')
<div class="container-fluid">
	<h1>{{ $cancellationReason->id }}</h1>
	<div class="mb-3">
		<a href="{{ route('cancellation-reasons.index') }}" class="btn btn-secondary btn-sm me-1"> <i class="fa fa-arrow-right"></i> {{__('Back to')}} {{__('Cancellation Reasons')}}</a>
		{{-- <a href='{{ route('cancellation-reasons.edit', $cancellationReason) }}' class="btn btn-warning btn-sm me-1">{{ __('Edit') }} <i class="fa fa-edit"></i></a> --}}
	</div>
	<div class="card">
		<div class="card-body">
			<ul class="list-group list-group-flush">
				<li class="list-group-item">
					<strong>{{ __("Id") }}:</strong> {{ $cancellationReason->id }}
				</li>
				<li class="list-group-item">
					<strong>{{ __("Reason") }}:</strong> {{ $cancellationReason->reason }}
				</li>
				<li class="list-group-item">
					<strong>{{ __("Type") }}:</strong> {{ $cancellationReason->type }}
				</li>
				<li class="list-group-item">
					<strong>{{ __("Is Active") }}:</strong>
                    @if($cancellationReason->is_active)
                            <span class="text-success">
                                <i class="fa fa-check-circle"></i> {{ __('Yes') }}
                            </span>
                        @else
                            <span class="text-danger">
                                <i class="fa fa-times-circle"></i> {{ __('No') }}
                            </span>
                        @endif
				</li>
				<li class="list-group-item">
					<strong>{{ __("Created At") }}:</strong> {{ $cancellationReason->created_at?->diffForHumans()??'-' }}
				</li>
				<li class="list-group-item">
					<strong>{{ __("Updated At") }}:</strong> {{ $cancellationReason->updated_at?->diffForHumans()??'-' }}
				</li>
			</ul>
		</div>
        <div>
            <div class="card-footer">
                <a href='{{ route('cancellation-reasons.edit', $cancellationReason) }}' class="btn btn-warning btn-sm me-1">{{ __('Edit') }} <i class="fa fa-edit"></i></a>
            </div>
        </div>
	</div>
	<div class="mt-3">
		{{-- <form method='POST' action='{{ route('cancellation-reasons.destroy', $cancellationReason) }}' onsubmit='return confirm("Are you sure you want to delete this item?")'>
			<input type='hidden' name='_method' value='DELETE'>
			<button type='submit' class="btn btn-square btn-danger">{{ __('Delete') }}</button>
		</form> --}}
	</div>
</div>
@endsection
