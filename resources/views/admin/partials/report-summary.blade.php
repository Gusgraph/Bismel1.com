<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/admin/partials/report-summary.blade.php
// ======================================================
?>
<section class="ui-panel" aria-labelledby="admin-report-summary-title">
    <header class="ui-panel__header">
        <div class="ui-panel__heading">
            @include('partials.ui.icon', ['icon' => 'fa-solid fa-file-shield', 'tone' => 'rose', 'size' => 'xl'])
            <div>
                <p class="ui-panel__eyebrow">Snapshot</p>
                <h2 class="ui-panel__title" id="admin-report-summary-title">Admin report summary</h2>
            </div>
        </div>
        <p class="ui-panel__body">{{ $summary['details'] ?? 'Reporting summary is not available yet.' }}</p>
    </header>
    @include('partials.ui.summary-grid', ['items' => $summary['items'] ?? []])
</section>

<section class="ui-panel" aria-labelledby="admin-report-metrics-title">
    <header class="ui-panel__header">
        <div class="ui-panel__heading">
            @include('partials.ui.icon', ['icon' => 'fa-solid fa-chart-column', 'tone' => 'amber', 'size' => 'xl'])
            <div>
                <p class="ui-panel__eyebrow">Coverage</p>
                <h2 class="ui-panel__title" id="admin-report-metrics-title">Coverage overview</h2>
            </div>
        </div>
        <p class="ui-panel__body">These metrics keep the platform view readable by emphasizing coverage and posture over raw volume alone.</p>
    </header>
    @include('partials.ui.summary-grid', ['items' => $metrics ?? []])
</section>

<section class="ui-panel" aria-labelledby="admin-report-trend-title">
    <header class="ui-panel__header">
        <div class="ui-panel__heading">
            @include('partials.ui.icon', ['icon' => 'fa-solid fa-timeline', 'tone' => 'violet', 'size' => 'xl'])
            <div>
                <p class="ui-panel__eyebrow">Operational view</p>
                <h2 class="ui-panel__title" id="admin-report-trend-title">Status snapshot</h2>
            </div>
        </div>
        <p class="ui-panel__body">An area-by-area view of platform readiness, operational coverage, and oversight presence.</p>
    </header>
    @include('partials.ui.simple-table', ['columns' => $trendColumns ?? [], 'rows' => $trendRows ?? [], 'caption' => 'Platform coverage lanes and system posture notes.'])
</section>
