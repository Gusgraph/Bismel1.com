<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/customer/partials/dashboard-readiness.blade.php
// ======================================================
?>
<section class="dashboard-card dashboard-card--readiness">
    <header class="dashboard-card__header">
        <div class="dashboard-card__heading">
            @include('partials.ui.icon', ['icon' => 'fa-solid fa-list-check', 'tone' => 'emerald', 'size' => 'lg'])
            <div>
                <p class="dashboard-card__eyebrow">Side panel</p>
                <h3 class="dashboard-card__title">{{ $dashboardSurface['readinessPanel']['title'] ?? 'Trading Readiness' }}</h3>
            </div>
        </div>
        <p class="dashboard-card__body">{{ $dashboardSurface['readinessPanel']['message'] ?? 'Current desk checks are visible here.' }}</p>
    </header>

    @if (!empty($dashboardSurface['readinessPanel']['scoreLabel']) || !empty($dashboardSurface['readinessPanel']['scoreValue']))
        <p>
            <strong>{{ $dashboardSurface['readinessPanel']['scoreLabel'] ?? 'Readiness Score' }}</strong>
            <span>{{ $dashboardSurface['readinessPanel']['scoreValue'] ?? '' }}</span>
        </p>
    @endif

    <ul class="ui-list ui-record-list">
        @forelse (($dashboardSurface['readinessPanel']['items'] ?? []) as $item)
            <li>
                <div class="ui-list__stack">
                    <div class="ui-inline-copy">
                        <strong>{{ $item['label'] ?? 'Readiness check' }}</strong>
                        <span class="ui-list__meta">{{ $item['value'] ?? 'Unknown' }}</span>
                    </div>
                    @if (!empty($item['context']))
                        <span class="ui-list__meta">{{ $item['context'] }}</span>
                    @endif
                    @if (!empty($item['route']))
                        <span class="ui-list__meta"><a href="{{ route($item['route']) }}">Open {{ strtolower((string) ($item['label'] ?? 'related page')) }}</a></span>
                    @endif
                </div>
            </li>
        @empty
            <li>No readiness markers are available yet.</li>
        @endforelse
    </ul>
</section>
