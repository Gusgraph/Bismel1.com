<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/License/ApiLicenseInventory.php
// ======================================================

namespace App\Support\License;

class ApiLicenseInventory
{
    public static function items(): array
    {
        return [
            ['name' => 'Starter License Placeholder', 'value' => 'starter-license', 'note' => 'Illustrative entitlement only'],
            ['name' => 'Desk License Placeholder', 'value' => 'desk-license', 'note' => 'No activation pipeline attached'],
        ];
    }
}
