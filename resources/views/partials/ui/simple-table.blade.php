@php
    $columns = $columns ?? [];
    $rows = $rows ?? [];
    $caption = $caption ?? null;
@endphp

<div class="ui-table-wrap">
    <table class="ui-table" border="1" cellpadding="6" cellspacing="0">
        @if ($caption)
            <caption>{{ $caption }}</caption>
        @endif
        <thead>
            <tr>
                @forelse ($columns as $key => $label)
                    <th scope="col">{{ $label }}</th>
                @empty
                    <th scope="col">Column</th>
                @endforelse
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    @foreach ($columns as $key => $label)
                        <td>{{ $row[$key] ?? 'N/A' }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ max(count($columns), 1) }}">No rows available.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
