<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/Dashboard/CustomerStats.php
// =====================================================

namespace App\Support\Dashboard;

class CustomerStats
{
    public static function items(): array
    {
        return [
            [
                'label' => 'Account Status',
                'value' => 'Active',
                'description' => 'Placeholder account state.',
            ],
            [
                'label' => 'Billing State',
                'value' => 'Trial',
                'description' => 'Placeholder billing state.',
            ],
            [
                'label' => 'Broker State',
                'value' => 'Pending',
                'description' => 'Placeholder broker state.',
            ],
            [
                'label' => 'License State',
                'value' => 'Active',
                'description' => 'Placeholder license state.',
            ],
        ];
    }
}
