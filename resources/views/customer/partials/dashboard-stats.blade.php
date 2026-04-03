<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/customer/partials/dashboard-stats.blade.php
// ======================================================
?>
<section class="dashboard-band dashboard-band--primary" aria-labelledby="customer-dashboard-stats-title">
    <header class="dashboard-band__header">
        <div class="dashboard-band__heading">
            @include('partials.ui.icon', ['icon' => 'fa-solid fa-chart-line', 'tone' => 'sky', 'size' => 'xl'])
            <div>
                <p class="dashboard-band__eyebrow">Top strip</p>
                <h2 class="dashboard-band__title" id="customer-dashboard-stats-title">Equity, buying power, runtime state, broker sync, and automation state</h2>
            </div>
        </div>
        <p class="dashboard-band__body">These five markers stay first so you can judge account state, runtime posture, and broker freshness in one glance.</p>
    </header>
    @include('partials.ui.stat-list', ['items' => collect($page['stats'] ?? [])->map(fn ($stat) => [
        'label' => $stat['label'],
        'value' => $stat['value'],
        'context' => $stat['description'] ?? null,
        'icon' => $stat['icon'] ?? null,
        'tone' => $stat['tone'] ?? null,
    ])->all()])
</section>
