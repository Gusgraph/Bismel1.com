<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/partials/ui/stat-list.blade.php
// ======================================================
?>
@php
    $items = $items ?? [];
    $labelKey = $labelKey ?? 'label';
    $valueKey = $valueKey ?? 'value';
@endphp

<ul class="ui-list ui-stat-list">
    @forelse ($items as $item)
        @php
            $label = $item[$labelKey] ?? '';
            $value = $item[$valueKey] ?? '';
            $normalizedLabel = strtolower($label . ' ' . ($item['context'] ?? ''));
            $icon = $item['icon'] ?? match (true) {
                str_contains($normalizedLabel, 'account'),
                str_contains($normalizedLabel, 'workspace'),
                str_contains($normalizedLabel, 'member') => 'fa-solid fa-user-group',
                str_contains($normalizedLabel, 'billing'),
                str_contains($normalizedLabel, 'invoice'),
                str_contains($normalizedLabel, 'payment'),
                str_contains($normalizedLabel, 'plan') => 'fa-solid fa-wallet',
                str_contains($normalizedLabel, 'broker'),
                str_contains($normalizedLabel, 'connection') => 'fa-solid fa-plug-circle-bolt',
                str_contains($normalizedLabel, 'license'),
                str_contains($normalizedLabel, 'key') => 'fa-solid fa-key',
                str_contains($normalizedLabel, 'audit'),
                str_contains($normalizedLabel, 'event'),
                str_contains($normalizedLabel, 'oversight') => 'fa-solid fa-shield-halved',
                str_contains($normalizedLabel, 'health'),
                str_contains($normalizedLabel, 'system'),
                str_contains($normalizedLabel, 'runtime') => 'fa-solid fa-server',
                default => 'fa-solid fa-circle-nodes',
            };
            $tone = $item['tone'] ?? match (true) {
                str_contains($normalizedLabel, 'billing'),
                str_contains($normalizedLabel, 'invoice'),
                str_contains($normalizedLabel, 'payment'),
                str_contains($normalizedLabel, 'plan') => 'emerald',
                str_contains($normalizedLabel, 'broker'),
                str_contains($normalizedLabel, 'connection') => 'blue',
                str_contains($normalizedLabel, 'license'),
                str_contains($normalizedLabel, 'key') => 'violet',
                str_contains($normalizedLabel, 'audit'),
                str_contains($normalizedLabel, 'event'),
                str_contains($normalizedLabel, 'oversight'),
                str_contains($normalizedLabel, 'health'),
                str_contains($normalizedLabel, 'system') => 'rose',
                default => 'sky',
            };
        @endphp
        <li>
            <div class="ui-list__stack ui-list__stack--stat">
                <div class="ui-list__row">
                    @include('partials.ui.icon', ['icon' => $icon, 'tone' => $tone, 'size' => 'md'])
                    <div class="ui-list__content">
                        <strong class="ui-list__title">{{ $label }}</strong>
                        <span class="ui-list__value">{{ $value }}</span>
                    </div>
                </div>
                @if (!empty($item['context']))
                    <span class="ui-list__meta">{{ $item['context'] }}</span>
                @endif
            </div>
        </li>
    @empty
        <li>No items are available yet.</li>
    @endforelse
</ul>
