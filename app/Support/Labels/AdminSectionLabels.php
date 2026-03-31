<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/Labels/AdminSectionLabels.php
// =====================================================

namespace App\Support\Labels;

class AdminSectionLabels
{
    public static function make(): array
    {
        return [
            'dashboard' => [
                ['heading' => 'Accounts Summary', 'description' => 'Admin account oversight placeholder summary.'],
                ['heading' => 'System Summary', 'description' => 'Admin system placeholder summary.'],
                ['heading' => 'System Health', 'description' => 'Shared admin operational placeholder labels.'],
            ],
            'accounts' => [
                ['heading' => 'Account Queue', 'description' => 'Pending customer account review placeholder.'],
                ['heading' => 'Ownership Review', 'description' => 'Ownership placeholder checks.'],
                ['heading' => 'Status Checks', 'description' => 'Shared account status labels for admin pages.'],
            ],
            'system' => [
                ['heading' => 'Environment Health', 'description' => 'Runtime placeholder health checks.'],
                ['heading' => 'Service Readiness', 'description' => 'Service placeholder readiness notes.'],
                ['heading' => 'Operational Notes', 'description' => 'Shared operational placeholder notes.'],
            ],
        ];
    }
}
