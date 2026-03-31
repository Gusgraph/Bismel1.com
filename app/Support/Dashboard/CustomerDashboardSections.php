<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/Dashboard/CustomerDashboardSections.php
// =====================================================

namespace App\Support\Dashboard;

class CustomerDashboardSections
{
    public static function items(): array
    {
        return [
            [
                'title' => 'Account Summary',
                'description' => 'Workspace identity, owner, status, and team context for the current customer account.',
            ],
            [
                'title' => 'Billing Summary',
                'description' => 'Subscription posture, plan visibility, and invoice coverage for the active workspace.',
            ],
            [
                'title' => 'Broker Summary',
                'description' => 'Broker connection and credential readiness for the account you are trying to activate.',
            ],
            [
                'title' => 'License Summary',
                'description' => 'License and API key coverage so activation blockers are visible from the dashboard.',
            ],
        ];
    }
}
