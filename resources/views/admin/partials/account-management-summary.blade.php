<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/admin/partials/account-management-summary.blade.php
// ======================================================
?>
<ul class="ui-list ui-record-list">
    @forelse ($managementSummary as $item)
        <li>
            <div class="ui-list__stack">
                <div class="ui-inline-copy">
                    @if (!empty($item['route']))
                        <a href="{{ $item['route'] }}">{{ $item['label'] }}</a>
                    @else
                        <strong>{{ $item['label'] }}</strong>
                    @endif
                    <span>{{ $item['value'] }}</span>
                </div>
                @if (!empty($item['note']))
                    <small class="ui-list__meta">{{ $item['note'] }}</small>
                @endif
            </div>
        </li>
    @empty
        <li>Workspace records will appear here once accounts are available for admin review.</li>
    @endforelse
</ul>
