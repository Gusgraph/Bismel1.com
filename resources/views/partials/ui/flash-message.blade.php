<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/partials/ui/flash-message.blade.php
// ======================================================
?>
@if (session('status'))
    <section class="ui-card ui-card--flash ui-flash-message">
        @php
            $statusMessage = \App\Support\Display\SafeDisplay::sanitizedText((string) session('status'));
            $statusMeta = session('status_meta');
            $normalizedStatus = strtolower($statusMessage);
            $isIssue = str_contains($normalizedStatus, 'not saved')
                || str_contains($normalizedStatus, 'could not')
                || str_contains($normalizedStatus, 'error')
                || str_contains($normalizedStatus, 'failed');
            $statusHeading = is_array($statusMeta) && ! empty($statusMeta['heading'])
                ? \App\Support\Display\SafeDisplay::sanitizedText((string) $statusMeta['heading'])
                : ($isIssue ? 'Update issue' : 'Changes saved');
            $statusTone = is_array($statusMeta) && ! empty($statusMeta['tone'])
                ? \App\Support\Display\SafeDisplay::sanitizedText((string) $statusMeta['tone'])
                : ($isIssue ? 'amber' : 'emerald');
            $statusIcon = match ($statusTone) {
                'emerald' => 'fa-solid fa-circle-check',
                'amber' => 'fa-solid fa-circle-exclamation',
                'rose' => 'fa-solid fa-triangle-exclamation',
                default => 'fa-solid fa-circle-info',
            };
        @endphp
        <div class="ui-flash-message__header">
            @include('partials.ui.icon', ['icon' => $statusIcon, 'tone' => $statusTone, 'size' => 'md'])
            <div class="ui-flash-message__copy">
                <p class="ui-flash-message__title"><strong>{{ $statusHeading }}</strong></p>
                <p class="ui-flash-message__body">{{ $statusMessage }}</p>
            </div>
        </div>
    </section>
@endif
