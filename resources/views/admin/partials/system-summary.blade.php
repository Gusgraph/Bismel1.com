<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/admin/partials/system-summary.blade.php
// ======================================================
?>
<section class="dashboard-card dashboard-card--system">
    <header class="dashboard-card__header">
        <div class="dashboard-card__heading">
            @include('partials.ui.icon', ['icon' => 'fa-solid fa-server', 'tone' => 'rose', 'size' => 'lg'])
            <div>
                <p class="dashboard-card__eyebrow">System state</p>
                <h3 class="dashboard-card__title">{{ $dashboard['system']['title'] }}</h3>
            </div>
        </div>
        <p class="dashboard-card__body">{{ $dashboard['system']['message'] }}</p>
    </header>
    @include('partials.ui.stat-list', ['items' => $dashboard['system']['items'] ?? []])
</section>
