<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/admin/partials/dashboard-activity.blade.php
// ======================================================
?>
<section class="dashboard-card dashboard-card--activity">
    <header class="dashboard-card__header">
        <div class="dashboard-card__heading">
            @include('partials.ui.icon', ['icon' => 'fa-solid fa-chart-simple', 'tone' => 'sky', 'size' => 'lg'])
            <div>
                <p class="dashboard-card__eyebrow">Audit and activity</p>
                <h3 class="dashboard-card__title">{{ $dashboard['activity']['title'] }}</h3>
            </div>
        </div>
        <p class="dashboard-card__body">{{ $dashboard['activity']['message'] }}</p>
    </header>
    @include('partials.ui.stat-list', ['items' => $dashboard['activity']['items'] ?? []])
</section>
