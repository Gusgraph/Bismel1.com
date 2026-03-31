<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/admin/partials/audit-overview.blade.php
// ======================================================
?>
<ul class="ui-list ui-record-list">
    @forelse ($auditOverview as $item)
        <li>
            <div class="ui-list__stack">
                <div class="ui-inline-copy">
                    <strong>{{ $item['event'] }}</strong>
                    <span>{{ $item['note'] }}</span>
                </div>
            </div>
        </li>
    @empty
        <li>Audit overview updates will appear here once oversight activity is recorded.</li>
    @endforelse
</ul>
