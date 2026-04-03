<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/customer/partials/dashboard-activity.blade.php
// ======================================================
?>
@php
    $activityCard = $card ?? ($dashboardSurface['activityPanel'] ?? []);
@endphp

<section class="dashboard-card dashboard-card--activity">
    <header class="dashboard-card__header">
        <div class="dashboard-card__heading">
            @include('partials.ui.icon', ['icon' => $activityCard['icon'] ?? 'fa-solid fa-wave-square', 'tone' => $activityCard['tone'] ?? 'sky', 'size' => 'lg'])
            <div>
                <p class="dashboard-card__eyebrow">Control block</p>
                <h3 class="dashboard-card__title">{{ $activityCard['title'] ?? 'Latest Activity' }}</h3>
            </div>
        </div>
        <p class="dashboard-card__body">{{ $activityCard['description'] ?? $activityCard['message'] ?? 'Recent activity is grouped here.' }}</p>
    </header>

    <ul class="ui-list ui-record-list">
        @forelse (($activityCard['items'] ?? []) as $item)
            <li>
                <div class="ui-list__stack">
                    <div class="ui-inline-copy">
                        <strong>{{ $item['title'] ?? 'Activity' }}</strong>
                        @if (!empty($item['status']))
                            <span class="ui-list__meta">{{ $item['status'] }}</span>
                        @endif
                    </div>
                    @if (!empty($item['summary']))
                        <span class="ui-list__meta">{{ $item['summary'] }}</span>
                    @endif
                    @if (!empty($item['meta']))
                        <span class="ui-list__meta">{{ $item['meta'] }}</span>
                    @endif
                </div>
            </li>
        @empty
            <li>{{ $activityCard['empty'] ?? 'No recent activity is available yet.' }}</li>
        @endforelse
    </ul>

    @if (!empty($activityCard['route']))
        <p><a href="{{ route($activityCard['route']) }}">{{ $activityCard['routeLabel'] ?? 'Open activity' }}</a></p>
    @endif
</section>
