<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/customer/partials/broker-provider-list.blade.php
// ======================================================
?>
@include('partials.ui.record-list', [
    'items' => collect($providers ?? [])->map(fn ($provider) => [
        'label' => $provider['label'] ?? 'Provider',
        'value' => $provider['value'] ?? null,
        'status' => $provider['status'] ?? null,
    ])->all(),
    'emptyMessage' => 'No broker providers available.',
])
