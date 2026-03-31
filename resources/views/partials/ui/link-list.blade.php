@php
    $items = $items ?? [];
@endphp

<ul class="ui-list ui-link-list">
    @forelse ($items as $item)
        <li>
            <div class="ui-list__stack">
                @if (!empty($item['route']))
                    <a href="{{ route($item['route']) }}">{{ $item['label'] ?? 'Link' }}</a>
                @else
                    <strong>{{ $item['label'] ?? 'Link' }}</strong>
                @endif
                @if (!empty($item['description']))
                    <span class="ui-list__meta">{{ $item['description'] }}</span>
                @endif
            </div>
        </li>
    @empty
        <li>No links available.</li>
    @endforelse
</ul>
