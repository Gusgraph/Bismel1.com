@php
    $items = $items ?? [];
    $emptyMessage = $emptyMessage ?? 'No records are available yet.';
    $meta = $meta ?? null;
@endphp

@if ($meta)
    <p class="ui-list__meta"><small>{{ $meta }}</small></p>
@endif

<ul class="ui-list ui-record-list">
    @forelse ($items as $item)
        @php
            $title = $item['title'] ?? $item['label'] ?? 'Record';
            $summary = $item['summary'] ?? $item['value'] ?? null;
            $route = $item['route'] ?? null;
            $status = $item['status'] ?? null;
            $details = $item['details'] ?? [];
        @endphp
        <li>
            <div class="ui-list__stack">
                <div class="ui-inline-copy">
                    @if ($route)
                        <a href="{{ $route }}">{{ $title }}</a>
                    @else
                        <strong>{{ $title }}</strong>
                    @endif
                    @if ($status)
                        @include('partials.ui.status-badge', ['status' => $status])
                    @endif
                </div>
                @if ($summary)
                    <div class="ui-list__meta"><small>{{ $summary }}</small></div>
                @endif
                @if (!empty($details))
                    <div class="ui-list__meta">
                        <small>
                            @foreach ($details as $detail)
                                {{ $detail['label'] }}: {{ $detail['value'] }}@if (! $loop->last) | @endif
                            @endforeach
                        </small>
                    </div>
                @endif
            </div>
        </li>
    @empty
        <li>{{ $emptyMessage }}</li>
    @endforelse
</ul>
