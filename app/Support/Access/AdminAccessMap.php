<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/Access/AdminAccessMap.php
// =====================================================

namespace App\Support\Access;

class AdminAccessMap
{
    public static function items(): array
    {
        return [
            'admin.dashboard' => 'Admin overview placeholder access',
            'admin.accounts.index' => 'Admin account oversight placeholder access',
            'admin.system.index' => 'Admin system placeholder access',
        ];
    }
}
