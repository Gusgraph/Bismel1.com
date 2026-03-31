<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/customer/partials/broker-credential-checklist.blade.php
// ======================================================
?>
<ul class="ui-list ui-record-list">
    @forelse ($brokerCredentialChecklist as $item)
        <li>
            <div class="ui-list__stack">
                <div class="ui-inline-copy">
                    <strong>{{ $item['label'] }}</strong>
                    <span>{{ $item['value'] }}</span>
                </div>
            </div>
        </li>
    @empty
        <li>No saved broker access is available yet.</li>
    @endforelse
</ul>
