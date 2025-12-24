@extends('layouts.app')
@php
	$currentPage = 'ads';
@endphp
@section('title', $ad->title)
@section('content')
<div class="container-fluid">
	<h1>{{ $ad->title }}</h1>
	<div class="mb-3">
		<a href="{{ route('ads.index') }}" class="btn btn-secondary btn-sm me-1"> <i class="fa fa-arrow-right"></i> {{__('Back to')}} {{__('Ads')}}</a>
		<a href='{{ route('ads.edit', $ad) }}' class="btn btn-warning btn-sm me-1">{{ __('Edit') }} <i class="fa fa-edit"></i></a>
	</div>
	<div class="card">
		<div class="card-body">
			<ul class="list-group list-group-flush">
				<li class="list-group-item">
					<strong>{{ __("Id") }}:</strong> {{ $ad->id }}
				</li>
				<li class="list-group-item">
					<strong>{{ __("Title") }}:</strong> {{ $ad->title }}
				</li>
				<li class="list-group-item">
					<strong>{{ __("Description") }}:</strong> {{ $ad->description }}
				</li>
				<li class="list-group-item">
					<strong>{{ __("Image") }}:     </strong> @if ($ad->image)
							<a href="{{ $ad->image_link }}" target="_blank">
								<img src="{{ $ad->image_link }}" style="width: 100px;">
							</a>
						@endif
				</li>
				<li class="list-group-item">
					<strong>{{ __("Created At") }}:</strong> {{ $ad->created_at?->diffForHumans()?? '-' }}
				</li>
				<li class="list-group-item">
					<strong>{{ __("Updated At") }}:</strong> {{ $ad->updated_at?->diffForHumans()?? '-' }}
				</li>
			</ul>
		</div>
	</div>
	<div class="mt-3">
		{{-- <form method='POST' action='{{ route('ads.destroy', $ad) }}' onsubmit='return confirm("Are you sure you want to delete this item?")'>
			<input type='hidden' name='_method' value='DELETE'>
			<button type='submit' class="btn btn-square btn-danger">{{ __('Delete') }}</button>
		</form> --}}
	</div>
</div>
@endsection