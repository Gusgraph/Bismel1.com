<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/partials/ui/info-card.blade.php
// ======================================================
?>
@php
    $title = $title ?? null;
    $body = $body ?? null;
    $symbol = $symbol ?? null;
    $icon = $icon ?? null;
    $tone = $tone ?? null;
    $mutedSymbols = ['﷽', 'ﷻ', 'ﷺ'];
    $symbolStyle = in_array($symbol, $mutedSymbols, true)
        ? 'color: rgba(148, 163, 184, 0.18);'
        : null;

    if (! $icon && $title) {
        $normalizedTitle = strtolower($title);

        $iconMatches = [
            'chart-line' => ['dashboard', 'report', 'summary', 'signals', 'status'],
            'triangle-exclamation' => ['alert', 'issue', 'warning'],
            'user-group' => ['team', 'member', 'account'],
            'wallet' => ['billing', 'invoice', 'subscription', 'plan'],
            'plug-circle-bolt' => ['broker', 'connection'],
            'key' => ['license', 'key'],
            'sliders' => ['setting', 'system', 'configuration'],
            'list-check' => ['checklist', 'flow', 'action'],
            'shield-halved' => ['audit', 'oversight', 'health', 'safe'],
        ];

        foreach ($iconMatches as $candidateIcon => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($normalizedTitle, $keyword)) {
                    $icon = 'fa-solid fa-' . $candidateIcon;
                    break 2;
                }
            }
        }

        if (! $tone) {
            $tone = match (true) {
                str_contains($normalizedTitle, 'alert'),
                str_contains($normalizedTitle, 'issue'),
                str_contains($normalizedTitle, 'warning') => 'amber',
                str_contains($normalizedTitle, 'billing'),
                str_contains($normalizedTitle, 'invoice'),
                str_contains($normalizedTitle, 'subscription'),
                str_contains($normalizedTitle, 'plan') => 'emerald',
                str_contains($normalizedTitle, 'broker'),
                str_contains($normalizedTitle, 'connection') => 'blue',
                str_contains($normalizedTitle, 'license'),
                str_contains($normalizedTitle, 'key') => 'violet',
                str_contains($normalizedTitle, 'audit'),
                str_contains($normalizedTitle, 'health'),
                str_contains($normalizedTitle, 'system') => 'rose',
                default => 'sky',
            };
        }
    }
@endphp

<section class="ui-card ui-card--info">
    @if ($title || $icon || $symbol)
        <div class="ui-card__headline">
            @if ($icon)
                @include('partials.ui.icon', ['icon' => $icon, 'tone' => $tone ?? 'sky', 'size' => 'lg'])
            @endif

            <div class="ui-card__heading-copy">
                @if ($title)
                    <p class="ui-card__title"><strong>{{ $title }}</strong>@if ($symbol) <small aria-hidden="true" @if($symbolStyle) style="{{ $symbolStyle }}" @endif>{{ $symbol }}</small>@endif</p>
                @elseif ($symbol)
                    <p><small aria-hidden="true" @if($symbolStyle) style="{{ $symbolStyle }}" @endif>{{ $symbol }}</small></p>
                @endif
            </div>
        </div>
    @endif

    @if ($body)
        <p class="ui-card__body">{{ $body }}</p>
    @endif

    {{ $slot ?? '' }}
</section>
