<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/partials/ui/firestore-summary-card.blade.php
// ======================================================
?>
@php
    $title = $title ?? 'Runtime Support Summary';
    $summary = $summary ?? [];
@endphp

<div class="customer-card-group">
    @include('partials.ui.info-card', [
        'title' => $title,
        'body' => $summary['details'] ?? 'Runtime-support summary is unavailable.',
        'icon' => 'fa-solid fa-database',
        'tone' => ($summary['status'] ?? null) === 'ok' ? 'sky' : 'amber',
    ])
    @include('partials.ui.stat-list', ['items' => $summary['items'] ?? []])
</div>
