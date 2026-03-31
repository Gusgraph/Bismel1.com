<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/Dashboard/AdminDashboardSections.php
// =====================================================

namespace App\Support\Dashboard;

class AdminDashboardSections
{
    public static function items(): array
    {
        return [
            [
                'title' => 'Accounts Summary',
                'description' => 'Workspace, owner, member, subscription, and invoice coverage across the platform.',
            ],
            [
                'title' => 'System Summary',
                'description' => 'Runtime mode, review channel, status level, and platform readiness in one control lane.',
            ],
            [
                'title' => 'System Health',
                'description' => 'The minimum operating signals needed to judge whether the local platform is configured and active.',
            ],
        ];
    }
}
