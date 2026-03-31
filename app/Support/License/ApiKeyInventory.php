<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/License/ApiKeyInventory.php
// ======================================================

namespace App\Support\License;

class ApiKeyInventory
{
    public static function items(): array
    {
        return [
            ['name' => 'Primary Placeholder Key', 'value' => 'pk_live_placeholder', 'note' => 'Visible for admin review only'],
            ['name' => 'Sandbox Placeholder Key', 'value' => 'pk_test_placeholder', 'note' => 'Non-active placeholder record'],
        ];
    }
}
