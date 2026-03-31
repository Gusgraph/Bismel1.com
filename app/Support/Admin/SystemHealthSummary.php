<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/Admin/SystemHealthSummary.php
// ======================================================

namespace App\Support\Admin;

class SystemHealthSummary
{
    public static function items(): array
    {
        return [
            ['label' => 'Runtime Posture', 'value' => 'Review current runtime mode and blocked-account signals together'],
            ['label' => 'Broker Recovery', 'value' => 'Use readiness, broker sync, and reconciliation in that order when runtime degrades'],
            ['label' => 'Config Review', 'value' => 'Confirm runtime mode, review channel, and status level stay aligned to current operations'],
        ];
    }
}
