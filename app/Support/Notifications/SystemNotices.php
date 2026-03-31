<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/Notifications/SystemNotices.php
// ======================================================

namespace App\Support\Notifications;

class SystemNotices
{
    public static function items(): array
    {
        return [
            ['label' => 'Runtime Mode', 'value' => 'Placeholder only', 'status' => 'placeholder'],
            ['label' => 'Operational Review', 'value' => 'Manual review lane', 'status' => 'review'],
        ];
    }
}
