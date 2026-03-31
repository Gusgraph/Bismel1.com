<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/Billing/SubscriptionSummary.php
// ======================================================

namespace App\Support\Billing;

class SubscriptionSummary
{
    public static function items(): array
    {
        return [
            ['label' => 'Active Plan View', 'value' => 'Growth placeholder plan'],
            ['label' => 'Cycle State', 'value' => 'Monthly placeholder cadence'],
            ['label' => 'Upgrade Flow', 'value' => 'No live plan switching attached'],
        ];
    }
}
