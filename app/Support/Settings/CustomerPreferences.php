<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/Settings/CustomerPreferences.php
// ======================================================

namespace App\Support\Settings;

class CustomerPreferences
{
    public static function items(): array
    {
        return [
            ['label' => 'Timezone', 'value' => 'UTC placeholder'],
            ['label' => 'Notification Mode', 'value' => 'Digest placeholder'],
            ['label' => 'Workspace Theme', 'value' => 'Default placeholder'],
        ];
    }
}
