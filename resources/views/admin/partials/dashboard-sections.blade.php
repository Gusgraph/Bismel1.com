<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/admin/partials/dashboard-sections.blade.php
// ======================================================
?>
<section class="ui-panel" aria-labelledby="admin-dashboard-sections-title">
    <header class="ui-panel__header">
        <div>
            <p class="ui-panel__eyebrow">Control lanes</p>
            <h2 class="ui-panel__title" id="admin-dashboard-sections-title">What this control surface covers</h2>
        </div>
        <p class="ui-panel__body">These sections reflect the core admin areas used to oversee workspaces, readiness, billing, system posture, and operations.</p>
    </header>
    @include('partials.ui.link-list', ['items' => collect($page['sections'] ?? [])->map(fn ($section) => [
        'label' => $section['title'] ?? $section['heading'],
        'description' => $section['description'] ?? null,
    ])->all()])
</section>
