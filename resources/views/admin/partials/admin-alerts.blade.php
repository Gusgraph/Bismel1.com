<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/admin/partials/admin-alerts.blade.php
// ======================================================
?>
@php
    $alerts = $alerts ?? [];
@endphp

<ul class="ui-list ui-record-list">
    @forelse ($alerts as $alert)
        <li>
            <div class="ui-list__stack">
                <div class="ui-inline-copy">
                    <strong>{{ $alert['title'] ?? 'Update' }}</strong>
                    @include('partials.ui.status-badge', ['status' => $alert['status'] ?? 'placeholder'])
                </div>
                <span class="ui-list__meta">{{ $alert['message'] ?? '' }}</span>
            </div>
        </li>
    @empty
        <li>No operational updates are showing right now.</li>
    @endforelse
</ul>
