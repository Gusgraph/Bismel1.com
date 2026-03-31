<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/partials/ui/summary-grid.blade.php
// ======================================================
?>
@php
    $items = $items ?? [];
@endphp

<section class="ui-summary-grid">
    <div class="ui-summary-grid__items">
        @forelse ($items as $item)
            @php
                $label = $item['label'] ?? 'Item';
                $value = $item['value'] ?? 'Not available';
                $context = $item['context'] ?? null;
                $icon = $item['icon'] ?? null;
                $tone = $item['tone'] ?? null;
                $normalizedLabel = strtolower($label . ' ' . ($context ?? ''));

                if (! $icon) {
                    $icon = match (true) {
                        str_contains($normalizedLabel, 'account'),
                        str_contains($normalizedLabel, 'workspace'),
                        str_contains($normalizedLabel, 'tenant') => 'fa-solid fa-building-user',
                        str_contains($normalizedLabel, 'billing'),
                        str_contains($normalizedLabel, 'invoice'),
                        str_contains($normalizedLabel, 'payment'),
                        str_contains($normalizedLabel, 'plan') => 'fa-solid fa-wallet',
                        str_contains($normalizedLabel, 'broker'),
                        str_contains($normalizedLabel, 'connection') => 'fa-solid fa-plug-circle-bolt',
                        str_contains($normalizedLabel, 'license'),
                        str_contains($normalizedLabel, 'key') => 'fa-solid fa-key',
                        str_contains($normalizedLabel, 'audit'),
                        str_contains($normalizedLabel, 'oversight'),
                        str_contains($normalizedLabel, 'security') => 'fa-solid fa-shield-halved',
                        str_contains($normalizedLabel, 'system'),
                        str_contains($normalizedLabel, 'health'),
                        str_contains($normalizedLabel, 'runtime') => 'fa-solid fa-server',
                        default => 'fa-solid fa-chart-column',
                    };
                }

                if (! $tone) {
                    $tone = match (true) {
                        str_contains($normalizedLabel, 'billing'),
                        str_contains($normalizedLabel, 'invoice'),
                        str_contains($normalizedLabel, 'payment'),
                        str_contains($normalizedLabel, 'plan') => 'emerald',
                        str_contains($normalizedLabel, 'broker'),
                        str_contains($normalizedLabel, 'connection') => 'blue',
                        str_contains($normalizedLabel, 'license'),
                        str_contains($normalizedLabel, 'key') => 'violet',
                        str_contains($normalizedLabel, 'audit'),
                        str_contains($normalizedLabel, 'security') => 'rose',
                        str_contains($normalizedLabel, 'account'),
                        str_contains($normalizedLabel, 'workspace'),
                        str_contains($normalizedLabel, 'tenant') => 'amber',
                        default => 'sky',
                    };
                }
            @endphp
            <article class="ui-summary-grid__item">
                <div class="ui-summary-grid__header">
                    @include('partials.ui.icon', ['icon' => $icon, 'tone' => $tone, 'size' => 'lg'])
                    <p class="ui-summary-grid__label"><strong>{{ $label }}</strong></p>
                </div>
                <p class="ui-summary-grid__value">{{ $value }}</p>
                @if ($context)
                    <p class="ui-summary-grid__context"><small>{{ $context }}</small></p>
                @endif
            </article>
        @empty
            <p>No summary items available.</p>
        @endforelse
    </div>
</section>
