<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/customer/partials/license-key-status.blade.php
// ======================================================
?>
@include('partials.ui.record-list', [
    'items' => collect($keyStatuses ?? [])->map(fn ($status) => [
        'label' => $status['label'] ?? 'Status',
        'value' => $status['value'] ?? null,
        'summary' => $status['note'] ?? null,
    ])->all(),
    'emptyMessage' => 'API key status will appear here after a key is added.',
])
