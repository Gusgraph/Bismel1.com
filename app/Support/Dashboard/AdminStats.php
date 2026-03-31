<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/Dashboard/AdminStats.php
// =====================================================

namespace App\Support\Dashboard;

class AdminStats
{
    public static function items(): array
    {
        return [
            [
                'label' => 'Accounts Reviewed',
                'value' => '12',
                'description' => 'Placeholder review count.',
            ],
            [
                'label' => 'System State',
                'value' => 'Stable',
                'description' => 'Placeholder system state.',
            ],
            [
                'label' => 'Health Checks',
                'value' => '3/3',
                'description' => 'Placeholder operations summary.',
            ],
        ];
    }
}
