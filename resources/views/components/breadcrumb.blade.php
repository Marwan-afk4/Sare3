@props(['items' => []])

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        @foreach($items as $item)
            @if($loop->last)
                <li class="breadcrumb-item active" aria-current="page">
                    @if(isset($item['icon']))
                        <i class="{{ $item['icon'] }} me-1"></i>
                    @endif
                    {{ $item['title'] }}
                </li>
            @else
                <li class="breadcrumb-item">
                    @if(isset($item['url']))
                        <a href="{{ $item['url'] }}" class="text-decoration-none">
                            @if(isset($item['icon']))
                                <i class="{{ $item['icon'] }} me-1"></i>
                            @endif
                            {{ $item['title'] }}
                        </a>
                    @else
                        @if(isset($item['icon']))
                            <i class="{{ $item['icon'] }} me-1"></i>
                        @endif
                        {{ $item['title'] }}
                    @endif
                </li>
            @endif
        @endforeach
    </ol>
</nav>