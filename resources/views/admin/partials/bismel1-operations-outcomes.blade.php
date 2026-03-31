<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/admin/partials/bismel1-operations-outcomes.blade.php
// ======================================================
?>
@php
    $recentExecutionItems = $recentExecutionItems ?? [];
    $recentRiskItems = $recentRiskItems ?? [];
    $recentPositionItems = $recentPositionItems ?? [];
    $runtimeWarningItems = $runtimeWarningItems ?? [];
@endphp

<div class="admin-page__detail-grid">
    <div class="admin-card-group">
        @include('partials.ui.info-card', ['title' => 'Recent Execution Outcomes'])
        @include('partials.ui.record-list', ['items' => $recentExecutionItems, 'emptyMessage' => 'No recent execution outcomes are available yet.'])
    </div>

    <div class="admin-card-group">
        @include('partials.ui.info-card', ['title' => 'Recent Risk Blocks'])
        @include('partials.ui.record-list', ['items' => $recentRiskItems, 'emptyMessage' => 'No recent risk blocks are available yet.'])
    </div>

    <div class="admin-card-group">
        @include('partials.ui.info-card', ['title' => 'Recent Reconciliation Outcomes'])
        @include('partials.ui.record-list', ['items' => $recentPositionItems, 'emptyMessage' => 'No recent reconciliation or position-manager outcomes are available yet.'])
    </div>

    <div class="admin-card-group">
        @include('partials.ui.info-card', ['title' => 'Recent Runtime Warnings'])
        @include('partials.ui.record-list', ['items' => $runtimeWarningItems, 'emptyMessage' => 'No recent runtime warnings are available right now.'])
    </div>
</div>
