@php
    $status = $status ?? 'placeholder';
    $statusMeta = is_array($status)
        ? array_merge(['label' => 'Pending', 'tone' => 'neutral', 'value' => 'placeholder'], $status)
        : \App\Support\Display\SafeDisplay::statusMeta((string) $status);
@endphp

<small class="ui-status-badge ui-status-badge--{{ $statusMeta['tone'] }}">[{{ $statusMeta['label'] }}]</small>
