<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/admin/partials/bismel1-operations-summary.blade.php
// ======================================================
?>
@php
    $operationsOverview = $operationsOverview ?? [];
    $summaryItems = $operationsOverview['summary_items'] ?? [];
    $blockedReasonItems = $operationsOverview['blocked_reason_items'] ?? [];
    $brokerReadinessItems = $operationsOverview['broker_readiness_items'] ?? [];
    $recoveryOrderItems = $operationsOverview['recovery_order_items'] ?? [];
@endphp

@include('partials.ui.summary-grid', ['items' => $summaryItems])

<div class="admin-page__detail-grid">
    <div class="admin-card-group">
        @include('partials.ui.info-card', ['title' => 'Blocked Reason Categories'])
        @include('partials.ui.stat-list', ['items' => $blockedReasonItems])
        @if ($blockedReasonItems === [])
            <p class="ui-list__meta">No blocked automation categories are currently recorded.</p>
        @endif
    </div>

    <div class="admin-card-group">
        @include('partials.ui.info-card', ['title' => 'Broker Readiness Categories'])
        @include('partials.ui.stat-list', ['items' => $brokerReadinessItems])
        @if ($brokerReadinessItems === [])
            <p class="ui-list__meta">No broker readiness summaries are currently recorded.</p>
        @endif
    </div>

    <div class="admin-card-group">
        @include('partials.ui.info-card', ['title' => 'Recovery Order'])
        @include('partials.ui.stat-list', ['items' => $recoveryOrderItems])
    </div>
</div>
