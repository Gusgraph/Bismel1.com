<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/Notifications/CustomerAlerts.php
// ======================================================

namespace App\Support\Notifications;

class CustomerAlerts
{
    public static function items(): array
    {
        return [
            ['title' => 'Setup Readiness Note', 'message' => 'Customer automation setup remains illustrative only.', 'status' => 'placeholder'],
            ['title' => 'Control Review Note', 'message' => 'Billing, broker, and automation review flows are not active yet.', 'status' => 'review'],
        ];
    }
}
