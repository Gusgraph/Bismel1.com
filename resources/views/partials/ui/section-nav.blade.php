@php
    $title = match ($title ?? 'Sections') {
        'Customer Section Navigation' => 'Customer Workspace Navigation',
        'Admin Section Navigation' => 'Admin Workspace Navigation',
        default => $title ?? 'Sections',
    };
    $items = $items ?? [];
@endphp

@if ($items)
    <section class="ui-card ui-section-nav" aria-labelledby="{{ \Illuminate\Support\Str::slug($title, '-') }}-title">
        <h2 class="ui-card__title" id="{{ \Illuminate\Support\Str::slug($title, '-') }}-title"><strong>{{ $title }}</strong> <small aria-hidden="true">ﷻ</small></h2>
        <ul class="ui-list ui-list--tight">
            @foreach ($items as $item)
                <li>
                    <div class="ui-list__stack">
                        @if (!empty($item['route']))
                            <a href="{{ route($item['route']) }}">{{ $item['label'] ?? 'Section' }}</a>
                        @else
                            <strong>{{ $item['label'] ?? 'Section' }}</strong>
                        @endif
                        @if (!empty($item['description']))
                            <span class="ui-list__meta">{{ $item['description'] }}</span>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>
    </section>
@endif
