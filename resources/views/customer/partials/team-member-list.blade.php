<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/customer/partials/team-member-list.blade.php
// ======================================================
?>
@include('partials.ui.record-list', [
    'items' => collect($teamMembers ?? [])->map(fn ($member) => [
        'label' => $member['name'] ?? 'Member',
        'value' => $member['role'] ?? null,
        'status' => $member['status'] ?? null,
    ])->all(),
    'emptyMessage' => 'Team access will appear here once additional members are added to the workspace.',
])
