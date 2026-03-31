@php
    $items = $items ?? [];
@endphp

@if ($items)
    <nav aria-label="Breadcrumbs" class="ui-breadcrumbs">
        <p class="ui-breadcrumbs__list">
            @foreach ($items as $item)
                @if (!empty($item['route']))
                    <a href="{{ route($item['route']) }}">{{ $item['label'] ?? 'Page' }}</a>
                @else
                    <span>{{ $item['label'] ?? 'Page' }}</span>
                @endif
                @if (! $loop->last)
                    <span aria-hidden="true" class="ui-breadcrumbs__separator"> / </span>
                @endif
            @endforeach
            <small aria-hidden="true">﷽</small>
        </p>
    </nav>
@endif
