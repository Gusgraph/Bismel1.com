<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/partials/ui/form-actions.blade.php
// ======================================================
?>
@php
    $submitLabel = $submitLabel ?? 'Save changes';
    $cancelRoute = $cancelRoute ?? null;
    $cancelLabel = $cancelLabel ?? 'Back';
    $note = $note ?? null;
@endphp

<div class="ui-form-actions">
    @if ($note)
        <p class="ui-form-actions__note">{{ $note }}</p>
    @endif
    <div class="ui-form-actions__buttons">
        <button class="ui-button ui-button--primary" type="submit">{{ $submitLabel }}</button>
        @if ($cancelRoute)
            <a class="ui-button ui-button--secondary ui-button-link" href="{{ route($cancelRoute) }}">{{ $cancelLabel }}</a>
        @endif
    </div>
</div>
