@extends('layouts.app')
@php
	$currentPage = 'ads';
@endphp
@section('title', __('Ads'))
@section('content')
<div class="container-fluid">
	<h1 class="mb-3">{{__('Ads')}}</h1>
	<div class="mb-3">
		<a href="{{ route('ads.create') }}" class="btn btn-primary btn-sm me-1">{{__('Create Ad')}} <i class="fa fa-plus"></i></a>
	</div>
	<div class='main-card mb-3 card'>
		<div class='card-body'>
			<table class="mb-0 table table-hover">
				<tr>
					<th>
						<a href="{{ route('ads.index', ['sort' => 'image', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Image") }}
							@if($sortField === 'image')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th>
						<a href="{{ route('ads.index', ['sort' => 'title', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Title") }}
							@if($sortField === 'title')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th>
						<a href="{{ route('ads.index', ['sort' => 'description', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Description") }}
							@if($sortField === 'description')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th>
						<a href="{{ route('ads.index', ['sort' => 'created_at', 'order' => $sortOrder === 'asc' ? 'desc' : 'asc']) }}">
							{{ __("Created At") }}
							@if($sortField === 'created_at')<i class="text-danger">{{ $sortOrder === 'asc' ? '▼' : '▲' }}</i>@endif
						</a>
					</th>
					<th class="text-center">{{ __('Actions') }}</th>
				</tr>
				@foreach($ads as $ad)
				<tr>
					<td>
						@if ($ad->image)
							<a href="{{ $ad->image_link }}" target="_blank">
								<img src="{{ $ad->image_link }}" style="width: 100px;">
							</a>
						@endif
					</td>
					<td>{{ $ad->title }}</td>
					<td>{{ $ad->description }}</td>
					<td>{{ $ad->created_at?->diffForHumans()?? '-' }}</td>
					<td class="text-center">
						<a href='{{ route('ads.show', $ad) }}' class="btn btn-subtle-primary btn-sm me-1">{{ __("Details") }} <i class="fa fa-eye"></i></a>
						{{-- <a href='{{ route('ads.edit', $ad) }}' class="btn btn-subtle-warning btn-sm me-1">{{ __("Edit") }} <i class="fa fa-edit"></i></a> --}}
						<form action="{{ route('ads.destroy', $ad) }}" method="POST" class="d-inline"
                            onsubmit="return confirm('{{ __('Are you sure you want to delete this ad?') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-subtle-danger btn-sm">
                                {{ __('Delete') }} <i class="fa fa-trash"></i>
                            </button>
                        </form>
					</td>
				</tr>
				@endforeach
			</table>
			{{ $ads->links('pagination::custom') }}
		</div>
	</div>
</div>
@endsection