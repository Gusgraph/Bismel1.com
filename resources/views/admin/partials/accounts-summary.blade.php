<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/admin/partials/accounts-summary.blade.php
// ======================================================
?>
<section class="dashboard-card dashboard-card--account">
    <header class="dashboard-card__header">
        <div class="dashboard-card__heading">
            @include('partials.ui.icon', ['icon' => 'fa-solid fa-building-user', 'tone' => 'amber', 'size' => 'lg'])
            <div>
                <p class="dashboard-card__eyebrow">Account and tenant state</p>
                <h3 class="dashboard-card__title">{{ $dashboard['account']['title'] }}</h3>
            </div>
        </div>
        <p class="dashboard-card__body">{{ $dashboard['account']['message'] }}</p>
    </header>
    @include('partials.ui.stat-list', ['items' => $dashboard['account']['items'] ?? []])
</section>
