<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/partials/ui/page-intro.blade.php
// ======================================================
?>
@php
    $title = $title ?? ($page['title'] ?? 'Page');
    $subtitle = $subtitle ?? ($page['subtitle'] ?? ($page['intro'] ?? null));
    $description = $description ?? ($page['intro'] ?? null);
@endphp

<section class="ui-page-intro">
    <h1 class="ui-page-intro__title">{{ $title }}</h1>
    @if ($subtitle)
        <p class="ui-page-intro__subtitle">{{ $subtitle }}</p>
    @endif
    @if ($description && $description !== $subtitle)
        <p class="ui-page-intro__description">{{ $description }} <small aria-hidden="true">ﷺ</small></p>
    @endif
</section>
