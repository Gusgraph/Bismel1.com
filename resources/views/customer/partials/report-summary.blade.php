<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/customer/partials/report-summary.blade.php
// ======================================================
?>
<section class="ui-panel" aria-labelledby="customer-report-summary-title">
    <header class="ui-panel__header">
        <div class="ui-panel__heading">
            @include('partials.ui.icon', ['icon' => 'fa-solid fa-file-lines', 'tone' => 'sky', 'size' => 'xl'])
            <div>
                <p class="ui-panel__eyebrow">Snapshot</p>
                <h2 class="ui-panel__title" id="customer-report-summary-title">Customer report summary</h2>
            </div>
        </div>
        <p class="ui-panel__body">{{ $summary['details'] ?? 'Reporting summary is not available yet.' }}</p>
    </header>
    @include('partials.ui.summary-grid', ['items' => $summary['items'] ?? []])
</section>

<section class="ui-panel" aria-labelledby="customer-report-metrics-title">
    <header class="ui-panel__header">
        <div class="ui-panel__heading">
            @include('partials.ui.icon', ['icon' => 'fa-solid fa-chart-column', 'tone' => 'emerald', 'size' => 'xl'])
            <div>
                <p class="ui-panel__eyebrow">Coverage</p>
                <h2 class="ui-panel__title" id="customer-report-metrics-title">Workspace coverage</h2>
            </div>
        </div>
        <p class="ui-panel__body">These metrics keep the page clear by showing what is already in place across billing, access, and recent account activity.</p>
    </header>
    @include('partials.ui.summary-grid', ['items' => $metrics ?? []])
</section>

<section class="ui-panel" aria-labelledby="customer-report-trend-title">
    <header class="ui-panel__header">
        <div class="ui-panel__heading">
            @include('partials.ui.icon', ['icon' => 'fa-solid fa-arrow-trend-up', 'tone' => 'violet', 'size' => 'xl'])
            <div>
                <p class="ui-panel__eyebrow">Current view</p>
                <h2 class="ui-panel__title" id="customer-report-trend-title">Status snapshot</h2>
            </div>
        </div>
        <p class="ui-panel__body">A simple area-by-area view of what is active, ready, waiting, or still needs attention.</p>
    </header>
    @include('partials.ui.simple-table', ['columns' => $trendColumns ?? [], 'rows' => $trendRows ?? [], 'caption' => 'Customer workspace summary lanes and readiness notes.'])
</section>
