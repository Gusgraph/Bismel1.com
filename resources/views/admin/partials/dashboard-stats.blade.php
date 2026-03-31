<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/admin/partials/dashboard-stats.blade.php
// ======================================================
?>
<section class="ui-panel" aria-labelledby="admin-dashboard-stats-title">
    <header class="ui-panel__header">
        <div class="ui-panel__heading">
            @include('partials.ui.icon', ['icon' => 'fa-solid fa-chart-pie', 'tone' => 'rose', 'size' => 'xl'])
            <div>
                <p class="ui-panel__eyebrow">Operations snapshot</p>
                <h2 class="ui-panel__title" id="admin-dashboard-stats-title">Platform control signals</h2>
            </div>
        </div>
        <p class="ui-panel__body">The fastest way to judge account, billing, broker, license, and system coverage.</p>
    </header>
    @include('partials.ui.stat-list', ['items' => collect($page['stats'] ?? [])->map(fn ($stat) => [
        'label' => $stat['label'],
        'value' => $stat['value'],
        'context' => $stat['description'] ?? null,
    ])->all()])
</section>
