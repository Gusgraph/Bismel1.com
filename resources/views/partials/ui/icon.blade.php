<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/partials/ui/icon.blade.php
// ======================================================
?>
@php
    $icon = $icon ?? 'fa-solid fa-circle';
    $tone = $tone ?? 'slate';
    $size = $size ?? 'md';
@endphp

<span class="ui-icon ui-icon--{{ $tone }} ui-icon--{{ $size }}" aria-hidden="true">
    <i class="{{ $icon }}"></i>
</span>
