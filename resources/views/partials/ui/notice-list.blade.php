<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/partials/ui/notice-list.blade.php
// ======================================================
?>
@php
    $items = $items ?? [];
@endphp

<ul class="ui-list ui-record-list ui-record-list--notices">
    @forelse ($items as $item)
        <li>
            <div class="ui-list__stack">
                <div class="ui-inline-copy">
                    <strong>{{ $item['label'] ?? 'Notice' }}</strong>
                    <span>{{ $item['value'] ?? '' }}</span>
                    @include('partials.ui.status-badge', ['status' => $item['status'] ?? 'placeholder'])
                </div>
            </div>
        </li>
    @empty
        <li>No notices available.</li>
    @endforelse
</ul>
