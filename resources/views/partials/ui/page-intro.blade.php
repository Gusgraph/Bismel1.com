<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/partials/ui/page-intro.blade.php
// ======================================================
?>
@php
    $title = $title ?? ($page['title'] ?? 'Page');
    $subtitle = $subtitle ?? ($page['subtitle'] ?? ($page['intro'] ?? null));
    $description = $description ?? ($page['intro'] ?? null);
    $summary = $summary ?? null;
    $summaryTitle = $summary['title'] ?? null;
    $summaryBody = $summary['body'] ?? null;
    $summaryEyebrow = $summary['eyebrow'] ?? 'Workspace summary';
    $summaryIcon = $summary['icon'] ?? null;
    $summaryTone = $summary['tone'] ?? 'sky';
@endphp

<section class="ui-page-intro">
    <div class="ui-page-intro__layout">
        <div class="ui-page-intro__content">
            <h1 class="ui-page-intro__title">{{ $title }}</h1>
            @if ($subtitle)
                <p class="ui-page-intro__subtitle">{{ $subtitle }}</p>
            @endif
            @if ($description && $description !== $subtitle)
                <p class="ui-page-intro__description">{{ $description }} <small aria-hidden="true">ﷺ</small></p>
            @endif
        </div>

        @if ($summaryTitle || $summaryBody)
            <aside class="ui-page-intro__summary" aria-label="{{ $summaryEyebrow }}">
                <div class="ui-page-intro__summary-head">
                    @if ($summaryIcon)
                        @include('partials.ui.icon', ['icon' => $summaryIcon, 'tone' => $summaryTone, 'size' => 'lg'])
                    @endif
                    <div class="ui-page-intro__summary-copy">
                        <p class="ui-page-intro__summary-eyebrow">{{ $summaryEyebrow }}</p>
                        @if ($summaryTitle)
                            <p class="ui-page-intro__summary-title">{{ $summaryTitle }}</p>
                        @endif
                    </div>
                </div>

                @if ($summaryBody)
                    <p class="ui-page-intro__summary-body">{{ $summaryBody }}</p>
                @endif
            </aside>
        @endif
    </div>
</section>
